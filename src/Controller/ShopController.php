<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Order;
use App\Entity\OrderItem;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Request;


final class ShopController extends AbstractController
{
    #[Route('/shop', name: 'app_shop')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $products = $em->getRepository(Product::class)->findBy(
            ['isActive' => true],
            ['id' => 'DESC']
        );

        $cart = $request->getSession()->get('cart', []);
        $cartCount = array_sum($cart);
        $cartTotal = 0;

        if (!empty($cart)) {
            $cartProducts = $em->getRepository(Product::class)->findBy([
                'id' => array_keys($cart),
            ]);

            foreach ($cartProducts as $product) {
                $qty = $cart[$product->getId()] ?? 0;
                $cartTotal += $product->getPrice() * $qty;
            }
        }

        return $this->render('shop/index.html.twig', [
            'products' => $products,
            'cartCount' => $cartCount,
            'cartTotal' => $cartTotal,
        ]);
    }

    #[Route('/shop/product/{id}', name: 'app_shop_product')]
    public function product(Product $product): Response
    {
        if (!$product->isActive()) {
            throw $this->createNotFoundException();
        }

        return $this->render('shop/product.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/shop/checkout/{id}', name: 'shop_checkout')]
    public function checkout(Product $product, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        if (!$product->isActive()) {
            throw $this->createNotFoundException();
        }

        // 1) créer la commande
        $order = new Order();
        $order->setUser($user);
        $order->setStatus('pending');
        $order->setTotal($product->getPrice());

        // 2) créer la ligne
        $item = new OrderItem();
        $item->setOrderRef($order);
        $item->setProduct($product);
        $item->setQuantity(1);
        $item->setPrice($product->getPrice());

        $em->persist($order);
        $em->persist($item);
        $em->flush();

        \Stripe\Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

        $session = \Stripe\Checkout\Session::create([
            'mode' => 'payment',
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $product->getName(),
                    ],
                    'unit_amount' => $product->getPrice(),
                ],
                'quantity' => 1,
            ]],
            'success_url' => $this->generateUrl('shop_success', [], UrlGeneratorInterface::ABSOLUTE_URL)
                . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $this->generateUrl('shop_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);

        $order->setStripeSessionId($session->id);
        $em->flush();

        return new RedirectResponse($session->url);

    }

    #[Route('/shop/order-created/{id}', name: 'shop_order_created')]
    public function orderCreated(Order $order): Response
    {
        $user = $this->getUser();
        if (!$user || $order->getUser() !== $user) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('shop/order_created.html.twig', [
            'order' => $order,
        ]);
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

        if ($order->getStatus() === 'paid') {
            $request->getSession()->remove('cart');
        }

        return $this->render('shop/success.html.twig', [
            'order' => $order,
        ]);
    }

    #[Route('/shop/cancel', name: 'shop_cancel')]
    public function cancel(): Response
    {
        return $this->render('shop/cancel.html.twig');
    }

    #[Route('/account/orders', name: 'account_orders')]
    public function myOrders(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $orders = $em->getRepository(Order::class)->findBy(
            ['user' => $user],
            ['createdAt' => 'DESC']
        );

        return $this->render('account/orders.html.twig', [
            'orders' => $orders,
        ]);
    }
}
