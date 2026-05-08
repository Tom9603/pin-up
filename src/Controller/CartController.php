<?php

namespace App\Controller;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\StripeClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Order;
use App\Entity\OrderItem;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


class CartController extends AbstractController
{
    public function __construct(private StripeClient $stripe) {}

    #[Route('/cart', name: 'cart_index')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $cart = $request->getSession()->get('cart', []);

        $products = [];
        $total = 0;

        if ($cart) {
            $products = $em->getRepository(Product::class)->findBy([
                'id' => array_keys($cart),
            ]);

            foreach ($products as $product) {
                $total += $product->getPrice() * $cart[$product->getId()];
            }
        }

        return $this->render('cart/index.html.twig', [
            'products' => $products,
            'cart' => $cart,
            'total' => $total,
        ]);
    }

    #[Route('/cart/add/{id}', name: 'cart_add')]
    public function add(Product $product, Request $request): Response
    {
        if (!$product->isActive()) {
            throw $this->createNotFoundException();
        }

        $session = $request->getSession();
        $cart = $session->get('cart', []);

        $id = $product->getId();
        $cart[$id] = ($cart[$id] ?? 0) + 1;

        $session->set('cart', $cart);

        return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('app_shop'));
    }

    #[Route('/cart/checkout', name: 'cart_checkout')]
    public function checkout(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $session = $request->getSession();
        $cart = $session->get('cart', []);

        if (!$cart) {
            return $this->redirectToRoute('cart_index');
        }

        $products = $em->getRepository(Product::class)->findBy([
            'id' => array_keys($cart),
        ]);

        if (!$products) {
            return $this->redirectToRoute('cart_index');
        }

        $order = new Order();
        $order->setUser($user);
        $order->setStatus('pending');

        $total = 0;
        $lineItemsStripe = [];

        foreach ($products as $product) {
            if (!$product->isActive()) {
                continue;
            }

            $qty = $cart[$product->getId()];
            $price = $product->getPrice();

            // total
            $total += $price * $qty;

            $item = new OrderItem();
            $item->setOrderRef($order);
            $item->setProduct($product);
            $item->setQuantity($qty);
            $item->setPrice($price);

            $em->persist($item);

            $lineItemsStripe[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $product->getName(),
                    ],
                    'unit_amount' => $price,
                ],
                'quantity' => $qty,
            ];
        }

        if ($total < 50) {
            return new Response('Montant trop faible', 400);
        }

        $order->setTotal($total);
        $em->persist($order);
        $em->flush();

        $stripeSession = $this->stripe->checkout->sessions->create([
            'mode' => 'payment',
            'payment_method_types' => ['card'],
            'line_items' => $lineItemsStripe,
            'success_url' => $this->generateUrl('shop_success', [], UrlGeneratorInterface::ABSOLUTE_URL)
                . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $this->generateUrl('shop_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);

        $order->setStripeSessionId($stripeSession->id);
        $em->flush();

        return new RedirectResponse($stripeSession->url);
    }

    #[Route('/cart/update/{id}/{action}', name: 'cart_update')]
    public function update(Product $product, string $action, Request $request): Response
    {
        $session = $request->getSession();
        $cart = $session->get('cart', []);

        $id = $product->getId();
        if (!isset($cart[$id])) {
            return $this->redirectToRoute('cart_index');
        }

        if ($action === 'plus') {
            $cart[$id] += 1;
        } elseif ($action === 'minus') {
            $cart[$id] -= 1;
            if ($cart[$id] <= 0) {
                unset($cart[$id]);
            }
        }

        $session->set('cart', $cart);

        return $this->redirectToRoute('cart_index');
    }

    #[Route('/cart/remove/{id}', name: 'cart_remove')]
    public function remove(Product $product, Request $request): Response
    {
        $session = $request->getSession();
        $cart = $session->get('cart', []);

        unset($cart[$product->getId()]);
        $session->set('cart', $cart);

        return $this->redirectToRoute('cart_index');
    }

    #[Route('/cart/clear', name: 'cart_clear')]
    public function clear(Request $request): Response
    {
        $request->getSession()->remove('cart');
        return $this->redirectToRoute('cart_index');
    }
}
