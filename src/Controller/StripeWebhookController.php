<?php

namespace App\Controller;

use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

final class StripeWebhookController extends AbstractController
{
    #[Route('/stripe/webhook', name: 'stripe_webhook', methods: ['POST'])]
    public function webhook(Request $request, EntityManagerInterface $em, MailerInterface $mailer): Response
    {
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

        $order = null;

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

        if ($order->getStatus() === 'paid') {
            return new Response('Already paid', 200);
        }

        $order->setStatus('paid');
        $em->flush();

        $email = (new TemplatedEmail())
            ->from('no-reply@misspinupbretagne.fr')
            ->to($order->getUser()->getEmail())
            ->subject('Confirmation de commande n°' . $order->getId())
            ->htmlTemplate('emails/order_confirmation.html.twig')
            ->context(['order' => $order]);

        $mailer->send($email);

        $adminEmail = (new TemplatedEmail())
            ->from('no-reply@misspinupbretagne.fr')
            ->to('misspinupbretagne@gmail.com')
            ->subject('Nouvelle commande reçue #' . $order->getId())
            ->htmlTemplate('emails/new_order.html.twig')
            ->context(['order' => $order]);

        $mailer->send($adminEmail);

        return new Response('OK', 200);
    }
}
