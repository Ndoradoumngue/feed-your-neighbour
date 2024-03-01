<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Doctrine\Persistence\ManagerRegistry;


class LoginService
{
    private ManagerRegistry $doctrine;
    private UserPasswordHasherInterface $passwordHasher;
    private JWTTokenManagerInterface $jwtManager;

    public function __construct(ManagerRegistry $doctrine, UserPasswordHasherInterface $passwordHasher, JWTTokenManagerInterface $jwtManager)
    {
        $this->doctrine = $doctrine;
        $this->passwordHasher = $passwordHasher;
        $this->jwtManager = $jwtManager;
    }

    public function login(string $email, string $password): ?string
    {
        $manager = $this->doctrine->getManager();
        $user = $manager->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$user) {
            return null; // L'utilisateur n'a pas été trouvé
        }

        if ($this->passwordHasher->isPasswordValid($user, $password)) {
            $token = $this->jwtManager->create($user);
            return $token; // Connexion réussie, renvoie le jeton JWT
        }

        return null; // Mot de passe incorrect
    }
}
