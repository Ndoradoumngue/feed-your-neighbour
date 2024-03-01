<?php

namespace App\Service;

use App\Entity\Reservations;
use App\Entity\User;
use App\Entity\Announcements;
use App\Entity\PositionGPS;
use DateTimeImmutable;
use DateInterval;
use Doctrine\Persistence\ManagerRegistry as PersistenceManagerRegistry;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;



class AnnouncementsService
{
    private $doctrine;
    private $tokenStorage;
    private $client;
    public function __construct(PersistenceManagerRegistry $doctrine, TokenStorageInterface $tokenStorage, HttpClientInterface $client)
    {
        $this->doctrine = $doctrine;
        $this->tokenStorage = $tokenStorage;
        $this->client = $client;
    }

    public function createAnnouncement($data)
    {
        try {
            $token = $this->tokenStorage->getToken();
            $userId = $token->getUser()->getId();
            $currentDate = DateTimeImmutable::createFromFormat('Y-m-d', date('Y-m-d'));
            $dateInOneMonth = $currentDate->add(new DateInterval('P6D'));
            $entityManager = $this->doctrine->getManager();
            $announcements = new Announcements();
            $user = $entityManager->find(User::class, $userId);
            $announcements->setOwner($user);
            $announcements->setComplement($data['complement'] ?? '');
            $announcements->setDescription($data['description'] ?? '');
            $announcements->setDate($data['date'] ?? $currentDate);
            $announcements->setLimitDate($data['limit_date'] ?? $dateInOneMonth);
            $announcements->setTitle($data['title'] ?? '');
            $announcements->setContenu([]);
            $announcements->setListeCreneaux([]);

            if (isset($data['contenu']) && is_array($data['contenu'])) {
                foreach ($data['contenu'] as $contenuItem) {
                    // Assurez-vous que chaque élément du contenu a les clés nécessaires
                    if (isset($contenuItem['item'], $contenuItem['quantite'], $contenuItem['codeEAN'])) {
                        $announcements->addContenuItem(
                            $contenuItem['item'],
                            $contenuItem['quantite'],
                            $contenuItem['codeEAN']
                        );
                    }
                }
            }
            $announcements->setCategorie($data['categorie'] ?? '');
            $announcements->setNumeroRue($data['numero_rue'] ?? '');
            $announcements->setStatus($data['status'] ?? 0);
            $announcements->setRue($data['rue'] ?? '');
            $announcements->setCodePostal($data['code_postal'] ?? '');
            $announcements->setVille($data['ville'] ?? '');

            $geoLocationResult = $this->handleGeoLocation($announcements, $data);

            if (!empty($data['liste_creneaux']) && is_array($data['liste_creneaux'])) {
                foreach ($data['liste_creneaux'] as $creneau) {
                    // Make sure each element in the array has the necessary keys
                    if (isset($creneau['day'], $creneau['slot']['dateDebut'], $creneau['slot']['dateEnd'])) {
                        $announcements->addCreneau(
                            DateTimeImmutable::createFromFormat('Y-m-d', $creneau['day']),
                            DateTimeImmutable::createFromFormat('H:i:s', $creneau['slot']['dateDebut']),
                            DateTimeImmutable::createFromFormat('H:i:s', $creneau['slot']['dateEnd'])
                        );
                    }
                }
            }
            // Vérifiez si le champ listeCreneaux n'est pas vide avant de l'ajouter à la base de données et si le champ geolocalisation n'est pas vide
            if (!empty($announcements->getListeCreneaux()) && !empty($announcements->getContenu()) && !isset($geoLocationResult['message'])) {
                // Ajoutez votre logique d'insertion dans la base de données ici
                $entityManager->persist($announcements);
                $entityManager->flush();
                return null;
            } else {
                return $geoLocationResult;
            }

        } catch (\Exception $e) {
            return ['message' => 'Il y\'a eu une erreur lors de la création de l\'annonce.', 'status' => 500];
        }
    }

    private function handleGeoLocation(Announcements $announcements, array $data)
    {
        $address = $data['numero_rue'] . '+' . $data['rue'] . '+' . $data['code_postal'] . '+' . $data['ville'];
        $apiUrl = "https://nominatim.openstreetmap.org/search?format=json&q=" . $address;
        $entityManager = $this->doctrine->getManager();
        $response = $this->client->request('GET', $apiUrl);
        $dataGps = $response->toArray();
        if (!empty($dataGps)) {
            $latitude = $dataGps[0]['lat'];
            $longitude = $dataGps[0]['lon'];

            $positionGPS = new PositionGPS();
            $positionGPS->setLat($latitude)
                ->setLong($longitude)
                ->setAnnouncement($announcements)
                ->setTitle($data['title']);

            $entityManager->persist($positionGPS);
            $entityManager->flush();
        } else {
            return ['message' => 'Il y\'a eu une erreur lors de la récupération de la géolocalisation.', 'status' => 400];
        }
    }

    public function getAnnouncement(int $id)
    {
        $manager = $this->doctrine->getManager();
        $announcement = $manager->getRepository(Announcements::class)->findOneBy(['id' => $id]);
        if (!$announcement) {
            return null;
        }
        try {
            return $announcement;
        } catch (\Exception $e) {
            return ['message' => 'Il y\'a eu une erreur lors de la récupération de l\'annonce.', 'status' => 500];
        }

    }

    public function getBookedAnnouncements()
    {
        $manager = $this->doctrine->getManager();
        $announcements = $manager->getRepository(Announcements::class)->findBy(['status' => true]);
        if (!$announcements) {
            return null;
        }
        return $announcements;
    }

    public function getNotBookedAnnouncements()
    {
        $manager = $this->doctrine->getManager();
        $announcements = $manager->getRepository(Announcements::class)->findBy(['status' => false]);
        if (!$announcements) {
            return null;
        }
        return $announcements;
    }

    public function bookAnnouncement(int $id)
    {
        $message = "";
        $entityManager = $this->doctrine->getManager();
        $announcement = $entityManager->getRepository(Announcements::class)->find($id);

        if (!$announcement) {
            $message = 'Aucune annonce trouvée pour l\'ID ' . $id;
            return ['message' => $message, 'status' => 409];
        }
        try {
            $announcement->setStatus(true);
            $entityManager->flush();
        } catch (\Exception $e) {
            return ['message' => 'Il y\'a eu une erreur lors de la mise à jour de l\'annonce.'];
        }

        return null;
    }

    public function deleteAnnouncement(int $id)
    {
        $entityManager = $this->doctrine->getManager();
        $announcement = $entityManager->getRepository(Announcements::class)->find($id);
        if (!$announcement) {
            $message = 'Aucune annonce trouvée pour l\'ID ' . $id;
            return ['message' => $message, 'status' => 409];
        }
        if ($announcement->isStatus() == true) {
            $message = 'L\'annonce est déjà réservée, veuillez d\'abord la supprimer de la liste des réservations.';
            return ['message' => $message, 'status' => 409];
        }
        $entityManager->remove($announcement);
        $entityManager->flush();

        return null;
    }

    public function getAnnouncementsOwner()
    {
        $token = $this->tokenStorage->getToken();
        $userId = $token->getUser()->getId();
        $manager = $this->doctrine->getManager();
        $announcements = $manager->getRepository(Announcements::class)->findBy(['owner' => $userId]);
        if (!$announcements) {
            return null;
        }
        return $announcements;
    }

    public function getUserAnnouncements()
    {
        $token = $this->tokenStorage->getToken();
        $userId = $token->getUser()->getId();
        $manager = $this->doctrine->getManager();
        $user = $manager->getRepository(User::class)->find($userId);
        $announcements = $manager->getRepository(Announcements::class)->findBy(['owner' => $user]);
        if (!$announcements) {
            return null;
        }
        return $announcements;
    }

    public function getUserAnnouncementsNotBooked()
    {
        $token = $this->tokenStorage->getToken();
        $userId = $token->getUser()->getId();
        $manager = $this->doctrine->getManager();
        $user = $manager->getRepository(User::class)->find($userId);
        $announcements = $manager->getRepository(Announcements::class)->findBy(['owner' => $user, 'status' => false]);
        if (!$announcements) {
            return null;
        }
        return $announcements;
    }
    public function getUserAnnouncementsBooked()
    {
        $token = $this->tokenStorage->getToken();
        $userId = $token->getUser()->getId();
        $manager = $this->doctrine->getManager();
        $user = $manager->getRepository(User::class)->find($userId);
        $announcements = $manager->getRepository(Announcements::class)->findBy(['owner' => $user, 'status' => true]);
        if (!$announcements) {
            return null;
        }
        return $announcements;
    }

    public function getUsersBookedAnnouncements()
    {
        $token = $this->tokenStorage->getToken();
        $userId = $token->getUser()->getId();
        $manager = $this->doctrine->getManager();
        $user = $manager->getRepository(User::class)->find($userId);
        $announcement_list = $manager->getRepository(Reservations::class)->findBy(['benef' => $user]);
        if (!$announcement_list) {
            return null;
        }
        $announcements = [];
        foreach ($announcement_list as $announcement) {
            $announcements[] = $announcement->getAnnouncement();
        }
        return $announcements;
    }

    public function getAllAnnouncements()
    {
        $manager = $this->doctrine->getManager();
        $announcements = $manager->getRepository(Announcements::class)->findAll();
        if (!$announcements) {
            return null;
        }
        return $announcements;
    }

}