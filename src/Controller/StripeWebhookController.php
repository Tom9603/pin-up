<?php

namespace App\Controller;

use App\Entity\Order;
use App\Enum\OrderStatus;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Attribute\Route;

final class StripeWebhookController extends AbstractController
{
    #[Route('/stripe/webhook', name: 'stripe_webhook', methods: ['POST'])]
    public function webhook(
        Request $request,
        EntityManagerInterface $em,
        OrderRepository $orderRepository,
        MailerInterface $mailer,
        LoggerInterface $logger,
    ): Response {
        $payload = $request->getContent();
        $signature = $request->headers->get('stripe-signature');

        $secret = $_ENV['STRIPE_WEBHOOK_SECRET'] ?? '';
        if ($secret === '') {
            return new Response('Missing STRIPE_WEBHOOK_SECRET', 500);
        }

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $signature, $secret);
        } catch (\Throwable) {
            return new Response('Invalid signature', 400);
        }

        if ($event->type !== 'checkout.session.completed') {
            return new Response('Ignored', 200);
        }

        $session = $event->data->object;

        $order = $em->getRepository(Order::class)->findOneBy([
            'stripeSessionId' => $session->id,
        ]);

        if (!$order) {
            return new Response('Order not found', 200);
        }

        if ($order->isPaid()) {
            return new Response('Already paid', 200);
        }

        $order->setStatus(OrderStatus::Paid);

        if ($order->getInvoiceNumber() === null) {
            $order->setInvoiceNumber($orderRepository->nextInvoiceNumber());
        }

        // Stripe API 2025+ : adresse dans collected_information.shipping_details
        // Stripe API ancienne : adresse dans shipping_details directement
        $shipping = $session->collected_information->shipping_details
            ?? $session->shipping_details
            ?? null;
        $customerDetails = $session->customer_details ?? null;

        if ($shipping !== null) {
            $order->setShippingName($shipping->name ?? null);
            $address = $shipping->address ?? null;
            if ($address !== null) {
                $order->setShippingLine1($address->line1 ?? null);
                $order->setShippingLine2($address->line2 ?? null);
                $order->setShippingPostalCode($address->postal_code ?? null);
                $order->setShippingCity($address->city ?? null);
                $order->setShippingCountry($address->country ?? null);
            }
        }

        if ($customerDetails !== null && !empty($customerDetails->phone)) {
            $order->setShippingPhone($customerDetails->phone);
        }

        foreach ($order->getOrderItems() as $item) {
            $product = $item->getProduct();
            if ($product !== null && $product->getStock() !== null) {
                $product->setStock(max(0, $product->getStock() - $item->getQuantity()));
            }
        }

        $em->flush();

        $fromEmail = $_ENV['APP_FROM_EMAIL'] ?? 'contact@misspinupbretagne.fr';
        $adminEmails = array_map('trim', explode(',', $_ENV['APP_ADMIN_EMAIL'] ?? 'contact@misspinupbretagne.fr'));

        try {
            $mailer->send((new TemplatedEmail())
                ->from($fromEmail)
                ->to($order->getUser()->getEmail())
                ->subject('Confirmation de commande n°' . $order->getId())
                ->htmlTemplate('emails/order_confirmation.html.twig')
                ->context(['order' => $order]));
        } catch (\Throwable $e) {
            $logger->error('Order confirmation email failed', [
                'order' => $order->getId(),
                'error' => $e->getMessage(),
            ]);
        }

        try {
            $mailer->send((new TemplatedEmail())
                ->from($fromEmail)
                ->to(...$adminEmails)
                ->subject('Nouvelle commande reçue #' . $order->getId())
                ->htmlTemplate('emails/new_order.html.twig')
                ->context(['order' => $order]));
        } catch (\Throwable $e) {
            $logger->error('Admin notification email failed', [
                'order' => $order->getId(),
                'error' => $e->getMessage(),
            ]);
        }

        return new Response('OK', 200);
    }
}
