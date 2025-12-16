<?php

namespace App\Controller;

use App\Repository\EventRepository;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class EventsController extends AbstractController
{
    #[Route('/events', name: 'app_events')]
    public function index(ArticleRepository $articleRepository, CategoryRepository $categoryRepository): Response
    {
        // Récupération des articles et catégories
        $articles = $articleRepository->findBy([], ['date_publication' => 'DESC']);
        $categories = $categoryRepository->findAll();

        return $this->render('events/index.html.twig', [
            'articles' => $articles,
            'categories' => $categories,
        ]);
    }

    #[Route('/api/events', name: 'api_events')]
    public function events(EventRepository $eventRepository): JsonResponse
    {
        $events = $eventRepository->findAll();

        $data = [];
        foreach ($events as $event) {

            $imageUrl = null;
            if (count($event->getMedias()) > 0) {
                $firstMedia = $event->getMedias()->first();
                if ($firstMedia && method_exists($firstMedia, 'getFilename')) {
                    $imageUrl = '/media/' . $firstMedia->getFilename();
                }
            }

            $data[] = [
                'id' => $event->getId(),
                'title' => $event->getTitle(),
                'start' => $event->getStart()->format('Y-m-d H:i:s'),
                'end' => $event->getEnd()?->format('Y-m-d H:i:s'),
                'extendedProps' => [
                    'content' => $event->getContent(),
                    'imageUrl' => $imageUrl,
                    ]
            ];
        }

        return new JsonResponse($data);
    }
}
