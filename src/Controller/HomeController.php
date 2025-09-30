<?php

namespace App\Controller;

use App\Repository\ImageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(ImageRepository $imageRepository): Response
    {
        $images = $imageRepository->findBy([], ['createdAt' => 'DESC']);

        return $this->render('home/index.html.twig', [
            'images' => $images,
        ]);
    }
}
