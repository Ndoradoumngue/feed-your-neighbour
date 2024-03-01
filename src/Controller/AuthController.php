<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\RegisterService;
use App\Service\LoginService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;

class AuthController extends AbstractController
{
    #[Route('api/registration', name: 'app_registration', methods: ['POST'])]
    public function registration(RegisterService $registrationService, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        try {
            $result = $registrationService->createUser($data);
            if ($result === null) {
                return new JsonResponse(['message' => 'Utilisateur créé.'], 201);
            } else {
                return new JsonResponse($result, 400);
            }
        } catch (\Exception $e) {
            return new JsonResponse(['message' => 'Une erreur est survenue.'], 500);
        }
      
    }


    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(Request $request, LoginService $loginService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (isset($data['email']) && isset($data['password'])) {
            $email = $data['email'];
            $password = $data['password'];

            $token = $loginService->login($email, $password);

            if ($token !== null) {
                // Connexion réussie, renvoie le jeton JWT
                return new JsonResponse(['token' => $token]);
            }
        }

        return new JsonResponse(['message' => 'Email ou mot de passe incorrect.'], 401);

    }
}
