<?php
namespace App\Controller;

use App\Service\ChatService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ChatController extends AbstractController
{
    #[Route('/api/send-message', name: 'send_message', methods: ['POST'])]
    public function sendMessage(Request $request, ChatService $chatService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (isset($data['to']) && isset($data['message'])) {
            $to = $data['to'];
            $message = $data['message'];
            $currentUser = $this->getUser();
            if (!$currentUser) {
                return new JsonResponse(['message' => 'Utilisateur non connecté.'], 403);
            }
            $chatService->sendMessage($currentUser->getId(), $to, $message);
            return new JsonResponse(['success' => true]);
        }

        return new JsonResponse(['message' => 'Données manquantes.'], 400);
    }


    #[Route('/api/get-messages', name: 'get_messages', methods: ['GET'])]
    public function getMessages(Request $request, ChatService $chatService): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (isset($data['to'])) {
            $to = $data['to'];
            $currentUser = $this->getUser();
            if (!$currentUser) {
                return new JsonResponse(['message' => 'Utilisateur non connecté.'], 403);
            }
    
            try {
                $messages = $chatService->getMessagesChat($currentUser->getId(), $to);
                $messageContent = [];
                foreach ($messages as $message) {
                    $messageContent[] = [
                    'content' => $message->getContent(),
                    'CreatedAt' => $message->getCreatedAt()->format('Y-m-d H:i:s'),
                    'from' => $message->getSender()->getId(),
                    'to' => $message->getRecipient()->getId(),
                    ];
                }

                return $this->json($messageContent, 200);
            } catch (\Exception $e) {
                return new JsonResponse(['message' => $e->getMessage()], 400);
            }
        }
        return new JsonResponse(['message' => 'Données manquantes.'], 400);
    }


}