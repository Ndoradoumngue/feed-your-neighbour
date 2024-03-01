<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Announcements;
use App\Entity\Reservations;
use DateTimeImmutable;
use Doctrine\Persistence\ManagerRegistry as PersistenceManagerRegistry;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ReservationsService
{
    private $doctrine;
    private $tokenStorage;
    public function __construct(PersistenceManagerRegistry $doctrine, TokenStorageInterface $tokenStorage)
    {
        $this->doctrine = $doctrine;
        $this->tokenStorage = $tokenStorage;
    }


    public function getAnnouncementReservation($id_announcement){
        $entityManager = $this->doctrine->getManager();
        $announcement = $entityManager->find(Announcements::class, $id_announcement);
        $reservation = $entityManager->getRepository(Reservations::class)->findOneBy(['announcement' => $announcement]);
        if ($reservation == null) {
            $message = "Aucune réservation n'existe pour cette annonce.";
            return ['message' => $message, 'status' => 404];
        } else {
            return $reservation;
        }
    }
    
    public function deleteReservation($id_announcement)
    {
        $entityManager = $this->doctrine->getManager();
        $reservation = $this->getAnnouncementReservation($id_announcement);
        $announcement = $entityManager->find(Announcements::class, $id_announcement);
        if ($reservation == null) {
            $message = "Aucune réservation n'existe pour cette annonce.";
            return ['message' => $message, 'status' => 404];
        } else {
            $announcement->setStatus(false);
            $entityManager->remove($reservation);
            try {
                $entityManager->flush();
                return null;
            } catch (\Exception $e) {
                $message = "Une erreur est survenue lors de la suppression de la réservation.";
                return ['message' => $message, 'status' => 500];
            }
        }
    }

    public function createReservation($data)
    {
        $message = "";
        $token = $this->tokenStorage->getToken();
        $userId = $token->getUser()->getId();
        $entityManager = $this->doctrine->getManager();
        $reservation = new Reservations();
        $user = $entityManager->find(User::class, $userId);
        $announcement = $entityManager->find(Announcements::class, $data['announcement_id']);
        $reservation->setBenef($user);
        $start = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $data['start']);
        $end = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $data['end']);
        $reservation->setCreneauStart($start);
        $reservation->setCreneauEnd($end);
        $reservation->setAnnouncement($announcement);
        $reservation->setStatus($data['status']);
        $reservation->setComment($data['comment']);
        $existingReservation = $entityManager->getRepository(Reservations::class)->findOneBy(['announcement' => $announcement]);
        if ($existingReservation != null) {
            $message = "Une réservation existe déjà pour cette annonce.";
            return ['message' => $message, 'status' => 409];
        } else if ($announcement == null) {
            $message = "L'annonce n'existe pas.";
            return ['message' => $message, 'status' => 404];
        } else if ($announcement->getOwner() == $user) {
            $message = "L'utilisateur ne peut pas réserver sa propre annonce.";
            return ['message' => $message, 'status' => 403];
        } else {
            $entityManager->persist($reservation);
            try {
                $entityManager->flush();
                return null;
            } catch (\Exception $e) {
                return ['message' => $message, 'status' => 500];
            }
        }
    }

     public function getUserReservations()
    {
        $token = $this->tokenStorage->getToken();
        $userId = $token->getUser()->getId();
        $entityManager = $this->doctrine->getManager();
        $user = $entityManager->find(User::class, $userId);
        $reservations_list = $entityManager->getRepository(Reservations::class)->findBy(['benef' => $user]);
        if(!$reservations_list){
            return null;
        }
        $reservations = [];
        foreach ($reservations_list as $reservation) {
            $reservations[] = [
                'id' => $reservation->getId(),
                'announcement' => $reservation->getAnnouncement()->getId(),
                'start' => $reservation->getCreneauStart(),
                'end' => $reservation->getCreneauEnd(),
                'status' => $reservation->getStatus(),
                'comment' => $reservation->getComment()
            ];
        }
        return $reservations;
    }
}