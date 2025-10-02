<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Reservation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;

class ReservationController extends AbstractController
{
    #[Route('/api/reserve/{id}', name: 'api_reserve_event', methods: ['POST'])]
    public function reserve(Event $event, Security $security, EntityManagerInterface $em): JsonResponse
    {
        $user = $security->getUser();

        if (!$user) {
            return new JsonResponse(['error' => 'Non authentifié'], 401);
        }

        // Vérifie si l’utilisateur a déjà réservé cet event
        $existing = $em->getRepository(Reservation::class)->findOneBy([
            'user' => $user,
            'event' => $event,
        ]);

        if ($existing) {
            return new JsonResponse(['error' => 'Déjà réservé'], 400);
        }

        // Création de la réservation
        $reservation = new Reservation();
        $reservation->setUser($user);
        $reservation->setEvent($event);
        $reservation->setDateReservation(new \DateTime());

        $em->persist($reservation);
        $em->flush();

        return new JsonResponse(['success' => true]);
    }

    #[Route('/api/event/{id}/reservations', name: 'api_event_reservations')]
    public function list(Event $event): JsonResponse
    {
        $data = [];
        foreach ($event->getReservations() as $reservation) {
            $data[] = $reservation->getUser()->getName();
        }

        return new JsonResponse($data);
    }

}
