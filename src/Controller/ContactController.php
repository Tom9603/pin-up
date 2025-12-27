<?php

namespace App\Controller;

use App\Form\ContactFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    public function index(Request $request, MailerInterface $mailer, HttpClientInterface $httpClient): Response
    {
        $form = $this->createForm(ContactFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $token = $request->request->get('g-recaptcha-response');

            $googleResponse = $httpClient->request('POST', 'https://www.google.com/recaptcha/api/siteverify', [
                'body' => [
                    'secret' => $_ENV['RECAPTCHA_SECRET_KEY'],
                    'response' => $token,
                ],
            ]);

            $recaptcha = $googleResponse->toArray(false);

            $ok =
                ($recaptcha['success'] ?? false) === true &&
                ($recaptcha['action'] ?? '') === 'contact' &&
                (float)($recaptcha['score'] ?? 0.0) >= 0.5;

            if (!$ok) {
                return $this->render('contact/index.html.twig', [
                    'contactForm' => $form->createView(),
                    'confirmation' => null,
                    'error' => 'Validation anti-bot échouée. Veuillez réessayer.',
                    'recaptcha_site_key' => $this->getParameter('recaptcha_site_key'),
                ]);
            }

            $data = $form->getData();

            $email = (new Email())
                ->from('tom.ochietti@gmail.com')
                ->to('tom.ochietti@gmail.com')
                ->subject('Nouveau message de contact')
                ->html(
                    $this->renderView('emails/contact.html.twig', [
                        'nom' => $data['nom'],
                        'email' => $data['email'],
                        'message' => $data['message'],
                    ])
                );

            $mailer->send($email);

            return $this->render('contact/index.html.twig', [
                'contactForm' => $form->createView(),
                'confirmation' => 'Votre message a été envoyé.',
                'error' => null,
                'recaptcha_site_key' => $this->getParameter('recaptcha_site_key'),
            ]);
        }

        return $this->render('contact/index.html.twig', [
            'contactForm' => $form->createView(),
            'confirmation' => null,
            'error' => null,
            'recaptcha_site_key' => $this->getParameter('recaptcha_site_key'),
        ]);
    }
}
