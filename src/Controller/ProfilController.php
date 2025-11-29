<?php

namespace App\Controller;

use App\Form\ProfilType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class ProfilController extends AbstractController
{
    #[Route('/profil', name: 'app_profil')]
    public function index(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = $this->getUser();

        $form = $this->createForm(ProfilType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $plainPassword = $form->get('plainPassword')->getData();
            if ($plainPassword) {
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }

            $em->flush();
            return $this->render('profil/index.html.twig', [
                'profilForm' => $form->createView(),
                'message' => 'Modifications enregistrées.',
            ]);
        }

        return $this->render('profil/index.html.twig', [
            'profilForm' => $form->createView(),
        ]);
    }

    #[Route('/profil/delete', name: 'app_profil_delete', methods: ['POST'])]
    public function delete(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = $this->getUser();
        $password = $request->request->get('password');

        if (!$this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Échec de la vérification CSRF.');
            return $this->redirectToRoute('app_profil');
        }

        if (!$passwordHasher->isPasswordValid($user, $password)) {
            $this->addFlash('error', 'Mot de passe incorrect.');
            return $this->redirectToRoute('app_profil');
        }

        $em->remove($user);
        $em->flush();

        $this->container->get('security.token_storage')->setToken(null);
        $request->getSession()->invalidate();

        $this->addFlash('success', 'Votre compte a bien été supprimé.');
        return $this->redirectToRoute('app_home');
    }


}
