<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ShopController extends AbstractController
{
    #[Route('/shop', name: 'app_shop')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $products = $em->getRepository(Product::class)->findBy(
            ['isActive' => true],
            ['id' => 'DESC']
        );

        return $this->render('shop/index.html.twig', [
            'products' => $products,
            ...$this->getCartSummary($request, $em),
        ]);
    }

    #[Route('/shop/product/{id}', name: 'app_shop_product')]
    public function product(Product $product, Request $request, EntityManagerInterface $em): Response
    {
        if (!$product->isActive()) {
            throw $this->createNotFoundException();
        }

        return $this->render('shop/product.html.twig', [
            'product' => $product,
            ...$this->getCartSummary($request, $em),
        ]);
    }

    /**
     * @return array{cartCount: int, cartTotal: int}
     */
    private function getCartSummary(Request $request, EntityManagerInterface $em): array
    {
        $cart = $request->getSession()->get('cart', []);
        $count = array_sum($cart);
        $total = 0;

        if (!empty($cart)) {
            $cartProducts = $em->getRepository(Product::class)->findBy([
                'id' => array_keys($cart),
            ]);

            foreach ($cartProducts as $product) {
                $qty = $cart[$product->getId()] ?? 0;
                $total += $product->getPrice() * $qty;
            }
        }

        return ['cartCount' => $count, 'cartTotal' => $total];
    }

    #[Route('/shop/success', name: 'shop_success')]
    public function success(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $sessionId = $request->query->get('session_id');
        if (!$sessionId) {
            return $this->redirectToRoute('cart_index');
        }

        $order = $em->getRepository(Order::class)->findOneBy([
            'stripeSessionId' => $sessionId,
            'user' => $user,
        ]);

        if (!$order) {
            return $this->redirectToRoute('cart_index');
        }

        $request->getSession()->remove('cart');

        return $this->render('shop/success.html.twig', [
            'order' => $order,
        ]);
    }

    #[Route('/shop/cancel', name: 'shop_cancel')]
    public function cancel(): Response
    {
        return $this->render('shop/cancel.html.twig');
    }

    #[Route('/account/orders/{id}/invoice', name: 'account_invoice')]
    public function invoice(Order $order): Response
    {
        $user = $this->getUser();
        if (!$user || $order->getUser() !== $user) {
            throw $this->createAccessDeniedException();
        }

        if (!$order->isPaid()) {
            throw $this->createNotFoundException('Facture indisponible pour cette commande.');
        }

        $html = $this->renderView('pdf/invoice.html.twig', ['order' => $order]);

        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'Helvetica');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = $order->getInvoiceNumber()
            ? sprintf('facture-%s.pdf', $order->getInvoiceNumber())
            : sprintf('facture-%05d.pdf', $order->getId());

        return new Response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => sprintf('attachment; filename="%s"', $filename),
        ]);
    }
}
