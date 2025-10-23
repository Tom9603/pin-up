<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LegalController extends AbstractController
{
    #[Route('/mentions-legales', name: 'mentions_legales')]
    public function mentionsLegales(): Response
    {
        return $this->render('legal/mentions_legales.html.twig', [
            'body_class' => 'legal-page-body'
        ]);
    }

    #[Route('/politique-de-confidentialite', name: 'politique_confidentialite')]
    public function politiqueConfidentialite(): Response
    {
        return $this->render('legal/politique_confidentialite.html.twig', [
            'body_class' => 'legal-page-body'
        ]);
    }

    #[Route('/cgv', name: 'cgv')]
    public function cgv(): Response
    {
        return $this->render('legal/cgv.html.twig', [
            'body_class' => 'legal-page-body'
        ]);
    }
}
