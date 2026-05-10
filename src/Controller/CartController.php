<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Product;
use App\Enum\OrderStatus;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Stripe\StripeClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


class CartController extends AbstractController
{
    public function __construct(
        private StripeClient $stripe,
        private LoggerInterface $logger,
    ) {}

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

    #[Route('/cart/add/{id}', name: 'cart_add', methods: ['POST'])]
    public function add(Product $product, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('cart_add', $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        if (!$product->isActive()) {
            throw $this->createNotFoundException();
        }

        $session = $request->getSession();
        $cart = $session->get('cart', []);
        $id = $product->getId();
        $currentQty = $cart[$id] ?? 0;

        if ($product->getStock() !== null && $currentQty + 1 > $product->getStock()) {
            $this->addFlash('error', 'Stock insuffisant pour ce produit.');
            return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('app_shop'));
        }

        $cart[$id] = $currentQty + 1;
        $session->set('cart', $cart);

        return $this->redirect($request->headers->get('referer') ?? $this->generateUrl('app_shop'));
    }

    #[Route('/cart/checkout', name: 'cart_checkout', methods: ['POST'])]
    public function checkout(Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('cart_checkout', $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

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

        foreach ($products as $product) {
            if (!$product->isActive()) {
                continue;
            }
            $qty = $cart[$product->getId()];
            if ($product->getStock() !== null && $qty > $product->getStock()) {
                $this->addFlash('error', sprintf(
                    'Stock insuffisant pour "%s" (disponible : %d).',
                    $product->getName(),
                    $product->getStock()
                ));
                return $this->redirectToRoute('cart_index');
            }
        }

        $total = 0;
        $lineItemsStripe = [];
        $orderItems = [];

        foreach ($products as $product) {
            if (!$product->isActive()) {
                continue;
            }

            $qty = $cart[$product->getId()];
            $price = $product->getPrice();
            $total += $price * $qty;

            $item = new OrderItem();
            $item->setProduct($product);
            $item->setQuantity($qty);
            $item->setPrice($price);
            $orderItems[] = $item;

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
            $this->addFlash('error', 'Le montant minimum de commande est de 0,50 €.');
            return $this->redirectToRoute('cart_index');
        }

        $em->beginTransaction();

        try {
            $order = new Order();
            $order->setUser($user);
            $order->setStatus(OrderStatus::Pending);
            $order->setTotal($total);
            $em->persist($order);

            foreach ($orderItems as $item) {
                $item->setOrderRef($order);
                $em->persist($item);
            }

            $em->flush();

            $stripeSession = $this->stripe->checkout->sessions->create([
                'mode' => 'payment',
                'payment_method_types' => ['card'],
                'line_items' => $lineItemsStripe,
                'shipping_address_collection' => [
                    'allowed_countries' => ['FR', 'BE', 'CH', 'LU', 'MC'],
                ],
                'phone_number_collection' => [
                    'enabled' => true,
                ],
                'customer_email' => $user->getEmail(),
                'success_url' => $this->generateUrl('shop_success', [], UrlGeneratorInterface::ABSOLUTE_URL)
                    . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => $this->generateUrl('shop_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL),
            ]);

            $order->setStripeSessionId($stripeSession->id);
            $em->flush();
            $em->commit();

            return new RedirectResponse($stripeSession->url);
        } catch (\Throwable $e) {
            $em->rollback();
            $this->logger->error('Checkout failed', [
                'error' => $e->getMessage(),
                'user' => $user->getUserIdentifier(),
            ]);
            $this->addFlash('error', 'Une erreur est survenue lors de la création du paiement. Réessayez.');
            return $this->redirectToRoute('cart_index');
        }
    }

    #[Route('/cart/update/{id}/{action}', name: 'cart_update', methods: ['POST'])]
    public function update(Product $product, string $action, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('cart_update', $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $session = $request->getSession();
        $cart = $session->get('cart', []);
        $id = $product->getId();

        if (!isset($cart[$id])) {
            return $this->redirectToRoute('cart_index');
        }

        if ($action === 'plus') {
            if ($product->getStock() !== null && $cart[$id] + 1 > $product->getStock()) {
                $this->addFlash('error', 'Stock insuffisant pour "' . $product->getName() . '".');
                return $this->redirectToRoute('cart_index');
            }
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

    #[Route('/cart/remove/{id}', name: 'cart_remove', methods: ['POST'])]
    public function remove(Product $product, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('cart_remove', $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $session = $request->getSession();
        $cart = $session->get('cart', []);

        unset($cart[$product->getId()]);
        $session->set('cart', $cart);

        return $this->redirectToRoute('cart_index');
    }

    #[Route('/cart/clear', name: 'cart_clear', methods: ['POST'])]
    public function clear(Request $request): Response
    {
        if (!$this->isCsrfTokenValid('cart_clear', $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $request->getSession()->remove('cart');
        return $this->redirectToRoute('cart_index');
    }
}
