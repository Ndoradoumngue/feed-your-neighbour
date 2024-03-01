<?php

namespace App\Service;

use App\Entity\User;
use DateTime;
use Doctrine\Persistence\ManagerRegistry as PersistenceManagerRegistry;

class RegisterService{

    private $doctrine;

    public function __construct(PersistenceManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function createUser($data)
    {
        $entityManager = $this->doctrine->getManager();

        // Vérifiez si les données requises sont présentes
        if (!isset($data['email']) || !isset($data['password']) || !isset($data['username']) || !isset($data['first_name']) || !isset($data['last_name']) || !isset($data['birth_date']) || !isset($data['ville']) || !isset($data['departement']) || !isset($data['numero_rue']) || !isset($data['rue']) || !isset($data['code_postal']) || !isset($data['status_user']) || !isset($data['numero_tel']) || !isset($data['is_verified']) || !isset($data['complement'])) {
            return ['message' => 'Tous les champs sont requis.'];
        }

        // Créez un nouvel utilisateur
        $user = new User();
        $user->setEmail($data['email']);
        $user->setRoles(['ROLE_USER']);
        $user->setUsername($data['username']);
        // Utilisez bcrypt pour hacher le mot de passe
        $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);
        $user->setPassword($hashedPassword);
        $user->setFirstName($data['first_name']);
        $user->setLastName($data['last_name']);
        $birthDateStr = $data['birth_date'];
        $birthDate = DateTime::createFromFormat('d/m/Y', $birthDateStr);
        $user->setBirthDate($birthDate);
        $user->setVille($data['ville']);
        $user->setDepartement($data['departement']);
        $user->setNumeroRue($data['numero_rue']);
        $user->setRue($data['rue']);
        $user->setCodePostal($data['code_postal']);
        $user->setStatusUser($data['status_user']);
        $user->setNumeroTel($data['numero_tel']);
        $user->setIsVerified($data['is_verified']);
        $user->setComplement($data['complement']);
        // Enregistrez l'utilisateur dans la base de données
        $entityManager->persist($user);
        

        try {
            $entityManager->flush();
            return null; // Pas d'erreur, retourne null pour indiquer le succès
        } catch (\Exception $e) {
            // En cas d'erreur, retourne un message d'erreur
            return ['message' => 'Email ou mot de passe invalide.'];
        }
    }



}