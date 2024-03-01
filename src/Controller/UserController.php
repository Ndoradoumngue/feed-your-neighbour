<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\UserService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Exceptions\AnnouncementServiceException;
use App\Exceptions\AnnouncementNotFoundException;

class UserController extends AbstractController
{

    #[Route('api/user/getUserInfo', name: 'get_user_info', methods: ['GET'])]
    public function getUserInfo(UserService $userService): JsonResponse
    {
        try {
            $result = $userService->getUserInfo();
            if ($result['status'] === 200) {
                return new JsonResponse($result['user'], 200);
            }
            else {
                return new JsonResponse($result['message'], $result['status']);
            }
           
        } catch (AnnouncementServiceException $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }
    }
}