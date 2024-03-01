<?php

namespace App\Service;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;


class UserService
{

    private ManagerRegistry $doctrine;
    private JWTTokenManagerInterface $jwtManager;
    private TokenStorageInterface $tokenStorage;

    public function __construct(ManagerRegistry $doctrine, TokenStorageInterface $tokenStorage, JWTTokenManagerInterface $jwtManager)
    {
        $this->doctrine = $doctrine;
        $this->jwtManager = $jwtManager;
        $this->tokenStorage = $tokenStorage;
    }
    public function getUserInfo()
    {
        $token = $this->tokenStorage->getToken();
        $userId = $token->getUser()->getId();
        $entityManager = $this->doctrine->getManager();
        $user = $entityManager->find(User::class, $userId);
        if ($user == null) {
            $message = "L'utilisateur n'existe pas.";
            return ['message' => $message, 'status' => 404];
        } else {
            $user = [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'firstname' => $user->getFirstname(),
                'lastname' => $user->getLastname(),
                'numero_tel' => $user->getNumeroTel(),
                'numero_rue' => $user->getNumeroRue(),
                'rue' => $user->getRue(),
                'complement' => $user->getComplement(),
                'user_status'=> $user->isStatusUser() == true ? 1 : 0,
                'user_status_text'=> $user->isStatusUser() == true ? 'Donneur' : 'Receveur',
                'user_role' => $user->getRoles()[0],
                'code_postal' => $user->getCodePostal(),
                'ville' => $user->getVille(),
                'date_naissance' => $user->getBirthDate()->format('Y-m-d H:i:s'),
            ];
            return ['user' => $user, 'status' => 200];
        }
    }
}