<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\ReservationsService;
use App\Service\AnnouncementsService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Exceptions\AnnouncementServiceException;
use App\Exceptions\AnnouncementNotFoundException;



class ReservationsController extends AbstractController
{
    #[Route('api/reservations/createReservation', name: 'reservation_creation', methods: ['POST'])]
    public function createReservation(ReservationsService $reservationsService, AnnouncementsService $announcementsService, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        try {
            $result = $reservationsService->createReservation($data);
            if ($result !== null) {
                return new JsonResponse(['message' => $result['message']], $result['status']);
            }
            $announcementsService->bookAnnouncement($data['announcement_id']);
            return new JsonResponse(['message' => 'La reservation a été créée avec succès'], 200);
        } catch (AnnouncementServiceException $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('api/reservations/getReservationAnnouncement', name: 'reservation_announcement_get', methods: ['POST'])]
    public function getReservation(AnnouncementsService $announcementService, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (empty($data['id_announcement'])) {
            return $this->json(['error' => 'Missing required parameter: id_announcement'], 400);
        }

        $id = $data['id_announcement'];

        try {
            $announcement = $announcementService->getAnnouncement($id);

            if ($announcement === null) {
                return $this->json(['error' => 'Aucune annonce n\'existe pour cet id d\'annonce'], 400);
            }

            $reservationsData = [];
            $reservations = $announcement->getReservations();

            if (!$reservations->isEmpty()) {
                foreach ($reservations as $reservation) {
                    $reservationsData[] = [
                        'id' => $reservation->getId(),
                        'beneficiary_id' => $reservation->getBenef()->getId(),
                        'announcement_id' => $reservation->getAnnouncement()->getId(),
                        'creneau_start' => $reservation->getCreneauStart()->format('Y-m-d H:i:s'),
                        'creneau_end' => $reservation->getCreneauEnd()->format('Y-m-d H:i:s'),
                        'status' => $reservation->getStatus(),
                        'comment' => $reservation->getComment(),
                    ];
                }
            }

            $announcementData = [
                'reservation' => $reservationsData,
            ];

            return $this->json($announcementData, 200);
        } catch (AnnouncementNotFoundException $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        } catch (AnnouncementServiceException $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('api/reservations/deleteReservation', name: 'reservation_delete', methods: ['DELETE'])]
    public function deleteReservation(ReservationsService $reservationsService, AnnouncementsService $announcementsService, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (empty($data['id_announcement'])) {
            return $this->json(['error' => 'Missing required parameter: id_announcement'], 400);
        }
        $id_announcement = $data['id_announcement'];
        try {
            $announcement = $announcementsService->getAnnouncement($id_announcement);
            if ($announcement === null) {
               return $this->json(['error' => 'Aucune annonce n\'existe pour cet id d\'annonce'], 400);
            }
            $result = $reservationsService->deleteReservation($id_announcement);
            if ($result !== null) {
                return new JsonResponse(['message' => $result['message']], $result['status']);
            }
            return $this->json(['message' => 'La reservation a été supprimée avec succès'], 200);
        } catch (AnnouncementServiceException $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }

    }


    #[Route('api/reservations/getUserReservations', name: 'user_reservation_get', methods: ['GET'])]
    public function getUserReservation(ReservationsService $reservationsService, Request $request): JsonResponse
    {
        try {
            $reservations = $reservationsService->getUserReservations(); 
            if (empty($reservations)) {
                return $this->json([], 200);
            }

            $reservationsData = [];

            foreach ($reservations as $reservation) {
                $reservationsData[] = [
                    'id' => $reservation->getId(),
                    'beneficiary_id' => $reservation->getBenef()->getId(),
                    'announcement_id' => $reservation->getAnnouncement()->getId(),
                    'announcer_id' => $reservation->getAnnouncement()->getOwner()->getId(),
                    'announcer_firstname' => $reservation->getAnnouncement()->getOwner()->getFirstname(),
                    'announcer_lastname' => $reservation->getAnnouncement()->getOwner()->getLastname(),
                    'announcer_email' => $reservation->getAnnouncement()->getOwner()->getEmail(),
                    'creneau_start' => $reservation->getCreneauStart()->format('Y-m-d H:i:s'),
                    'creneau_end' => $reservation->getCreneauEnd()->format('Y-m-d H:i:s'),
                    'status' => $reservation->getStatus(),
                    'comment' => $reservation->getComment(),
                ];
            }
            return $this->json($reservationsData, 200);
        } catch (AnnouncementServiceException $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }
}