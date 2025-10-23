<?php

namespace App\Controller;

use App\Form\ContactFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

final class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    public function index(Request $request, MailerInterface $mailer): Response
    {
        $form = $this->createForm(ContactFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $email = (new Email())
                ->from('tom.ochietti@gmail.com')
                ->to('tom.ochietti@gmail.com')
                ->subject('Nouveau message de contact')
                ->text(
                    "Nom : {$data['nom']}\n" .
                    "Email : {$data['email']}\n" .
                    "Message : {$data['message']}"
                );

            $mailer->send($email);

            return $this->render('contact/index.html.twig', [
                'contactForm' => $form->createView(),
                'confirmation' => 'Envoyé.',
            ]);
        }

        return $this->render('contact/index.html.twig', [
            'contactForm' => $form->createView(),
            'confirmation' => null,
        ]);
    }
}
