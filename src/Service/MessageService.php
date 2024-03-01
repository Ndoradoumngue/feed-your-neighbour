<?php

namespace App\Service;

use App\Exception\MessageException;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Messages;
use App\Entity\User;

class MessageService
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function saveMessage(User $sender, User $recipient, $content)
    {
        $message = new Messages();
        $message->setContent($content);
        $message->setSender($sender);
        $message->setRecipient($recipient);
        $message->setCreatedAt(new \DateTime());
        try {
            $this->entityManager->persist($message);
            $this->entityManager->flush();
        } catch (\Exception $e) {
            throw new MessageException($e->getMessage());
        }
    }

    public function getMessages(User $user1, User $user2)
    {
        $messages = $this->entityManager->getRepository(Messages::class)->findBy([
            'sender' => $user1,
            'recipient' => $user2,
        ], [
            'createdAt' => 'ASC',
        ]);
    
        if (empty($messages)) {
            throw new MessageException('Aucun message trouvé.');
        }
    
        return $messages;
    }

}