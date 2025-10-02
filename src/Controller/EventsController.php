<?php

namespace App\Controller;

use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class EventsController extends AbstractController
{
    #[Route('/events', name: 'app_events')]
    public function index(): Response
    {
        return $this->render('events/index.html.twig');
    }
    #[Route('/api/events', name: 'api_events')]
    public function events(EventRepository $eventRepository): JsonResponse
    {
        $events = $eventRepository->findAll();

        $data = [];
        foreach ($events as $event) {
            $data[] = [
            'id' => $event->getId(),
            'title' => $event->getTitle(),
            'start' => $event->getStart()->format('Y-m-d H:i:s'),
            'end' => $event->getEnd()?->format('Y-m-d H:i:s'),
            'content' => $event->getContent(),

            ];
        }

        return new JsonResponse($data);
    }
}
