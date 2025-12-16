<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    public function __construct(private EmailVerifier $emailVerifier)
    {
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $hasher, Security $security, EntityManagerInterface $em): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user->setPassword($hasher->hashPassword($user, $form->get('plainPassword')->getData()));
            $em->persist($user);
            $em->flush();

            //$security->login($user);

            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('tom.ochietti@gmail.com', 'Comité Miss Pin-Up Bretagne'))
                    ->to($user->getEmail())
                    ->subject('Confirmez votre adresse e-mail')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );

            $this->addFlash('verify_banner', 'Un e-mail de confirmation vous a été envoyé.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, EntityManagerInterface $entityManager): Response
    {
        $userId = $request->query->get('id');

        if (!$userId) {
            $this->addFlash('verify_banner', 'Lien invalide.');
            return $this->redirectToRoute('app_login');
        }

        $user = $entityManager->getRepository(User::class)->find($userId);

        if (!$user) {
            $this->addFlash('verify_banner', 'Utilisateur introuvable.');
            return $this->redirectToRoute('app_login');
        }

        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
            $this->addFlash('verify_banner', 'Email confirmé, vous pouvez vous connecter.');
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_banner', 'Lien invalide ou expiré.');
        }

        return $this->redirectToRoute('app_login');
    }

    #[Route('/resend-verification', name: 'app_resend_verification')]
    public function resend(Request $request, EntityManagerInterface $em): Response
    {
        $email = $request->query->get('email');
        if (!$email) {
            $this->addFlash('verify_banner', 'Aucun e-mail fourni.');
            return $this->redirectToRoute('app_login');
        }

        $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$user) {
            $this->addFlash('verify_banner', 'E-mail inconnu.');
            return $this->redirectToRoute('app_login');
        }

        if ($user->isVerified()) {
            $this->addFlash('verify_banner', 'Ce compte est déjà vérifié.');
            return $this->redirectToRoute('app_login');
        }

        $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
            (new TemplatedEmail())
                ->from(new Address('tom.ochietti@gmail.com', 'Comité Miss Pin-Up Bretagne'))
                ->to($user->getEmail())
                ->subject('Confirmation de votre e-mail')
                ->htmlTemplate('registration/confirmation_email.html.twig')
        );

        $this->addFlash('verify_banner', 'E-mail de confirmation renvoyé.');
        return $this->redirectToRoute('app_login');
    }
}
