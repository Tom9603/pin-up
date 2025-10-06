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
            return new JsonResponse(['error' => 'Veuillez vous connecter pour vous inscrire'], 401);
        }

        $existing = $em->getRepository(Reservation::class)->findOneBy([
            'user' => $user,
            'event' => $event,
        ]);

        if ($existing) {
            return new JsonResponse(['error' => 'Vous êtes déjà inscrit'], 400);
        }

        $reservation = new Reservation();
        $reservation->setUser($user);
        $reservation->setEvent($event);
        $reservation->setDateReservation(new \DateTime());

        $em->persist($reservation);
        $em->flush();

        return new JsonResponse(['success' => true]);
    }

    #[Route('/api/unreserve/{id}', name: 'api_unreserve_event', methods: ['DELETE'])]
    public function unreserve(Event $event, Security $security, EntityManagerInterface $em): JsonResponse
    {
        $user = $security->getUser();

        if (!$user) {
            return new JsonResponse(['error' => "Vous ne pouvez pas annuler si vous n'êtes pas connecté", 401]);
        }

        $reservation = $em->getRepository(Reservation::class)->findOneBy([
            'user' => $user,
            'event' => $event,
        ]);

        if (!$reservation) {
            return new JsonResponse(['error' => 'Pas de réservation trouvée'], 404);
        }

        $em->remove($reservation);
        $em->flush();

        return new JsonResponse(['success' => true]);
    }

    #[Route('/api/event/{id}/reservations', name: 'api_event_reservations', methods: ['GET'])]
    public function getReservations(Event $event): JsonResponse
    {
        $users = [];
        foreach ($event->getReservations() as $reservation) {
            $users[] = $reservation->getUser()->getName();
        }

        return new JsonResponse($users);
    }
}
