<?php

namespace App\Service;

use App\Exception\MessageException;
use Doctrine\ORM\EntityManagerInterface;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\WampConnection;
use App\Service\MessageService;
use App\Entity\User;

class ChatService implements MessageComponentInterface
{
    protected $clients;
    protected $userConnections;
    protected $messageService;
    protected $entityManager;

    
    public function __construct(MessageService $messageService, EntityManagerInterface $entityManager)
    {
        $this->clients = new \SplObjectStorage;
        $this->userConnections = [];
        $this->messageService = $messageService;
        $this->entityManager = $entityManager;
    }



    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);

        echo "Nouvelle connexion! ({$this->getConnectionId($conn)})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $data = json_decode($msg);

        if (!isset($data->to) || !isset($data->message)) {
            return;
        }

        $to = $data->to;
        $message = $data->message;

        // Trouver la connexion du destinataire
        $toConnection = $this->getUserConnection($to);

        if ($toConnection !== null) {
            // Envoyer le message au destinataire
            $toConnection->send(json_encode([
                'from' => $this->getConnectionId($from),
                'message' => $message,
            ]));
        }
    }

    public function onMessageReceived(ConnectionInterface $from, $msg)
    {
        $data = json_decode($msg);

        if (!isset($data->to) || !isset($data->message)) {
            return;
        }

        $to = $data->to;
        $message = $data->message;

        // Trouver la connexion du destinataire
        $toConnection = $this->getUserConnection($to);

        if ($toConnection !== null) {
            // Envoyer le message au destinataire
            $toConnection->send(json_encode([
                'from' => $this->getConnectionId($from),
                'message' => $message,
            ]));
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);
        $userId = $this->getUserByConnection($conn);

        if ($userId !== null) {
            unset($this->userConnections[$userId]);
            echo "Connexion {$userId} a été déconnectée\n";
        }
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "Une erreur s'est produite : {$e->getMessage()}\n";

        $conn->close();
    }

    protected function getConnectionId(ConnectionInterface $conn)
    {
        if ($conn instanceof WampConnection) {
            return $conn->WAMP->sessionId;
        }

        return null;
    }

    protected function getUserByConnection(ConnectionInterface $conn)
    {
        foreach ($this->userConnections as $userId => $connection) {
            if ($connection === $conn) {
                return $userId;
            }
        }

        return null;
    }

    protected function getUserConnection($userId)
    {
        return isset($this->userConnections[$userId]) ? $this->userConnections[$userId] : null;
    }

    protected function getUserByUserId($userId)
    {
        // Utilisez Doctrine pour récupérer l'utilisateur par son ID
        return $this->entityManager->getRepository(User::class)->find($userId);
    }

    public function sendMessage($fromUserId, $to, $messageContent)
    {

        $sender = $this->getUserByUserId($fromUserId);
        $recipient = $this->getUserByUserId($to);

        if ($sender === null || $recipient === null) {
            throw new MessageException('Utilisateur inconnu.');
        }

        if($sender->getId() === $recipient->getId()){
            throw new MessageException('Vous ne pouvez pas vous envoyer un message.');
        }

        if($messageContent === ''){
            throw new MessageException('Vous ne pouvez pas envoyer un message vide.');
        }

        if ($sender !== null && $recipient !== null) {
            $this->messageService->saveMessage($sender, $recipient, $messageContent);
        }
        $toConnection = $this->getUserConnectionByUserId($to);
        $fromConnection = $this->getUserConnectionByUserId($fromUserId);

        if ($fromConnection !== null && $toConnection !== null) {
            $toConnection->send(json_encode([
                'from' => $this->getConnectionId($fromConnection),
                'message' => $messageContent,
            ]));
        }
    }

    protected function getUserConnectionByUserId($userId)
    {
       //TODO verifier si l'utilisateur est connecté
        if (isset($this->userConnections[$userId])) {
            return $this->userConnections[$userId];
        }

        return null;
    }

    
    public function getMessagesChat($fromUserId, $to)
    {
        $sender = $this->getUserByUserId($fromUserId);
        $recipient = $this->getUserByUserId($to);
        if ($sender === null || $recipient === null) {
            throw new MessageException('Utilisateur inconnu.');
        }
        if($sender->getId() === $recipient->getId()){
            throw new MessageException('Vous ne pouvez pas vous envoyer un message à vous même.');
        }
        $messages = $this->messageService->getMessages($sender, $recipient);
    
        $toConnection = $this->getUserConnectionByUserId($to);
        $fromConnection = $this->getUserConnectionByUserId($fromUserId);
        if ($fromConnection !== null && $toConnection !== null) {
            $toConnection->send(json_encode([
                'from' => $this->getConnectionId($fromConnection),
                'messages' => $messages,
            ]));
        }
        try {
            return $messages;
        } catch (\Exception $e) {
            throw new MessageException($e->getMessage());
        }
    
    }
}

