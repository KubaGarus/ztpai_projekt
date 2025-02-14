<?php

namespace App\Controller;

use App\Entity\Conversations;
use App\Entity\Documents;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/conversations')]
class ConversationsController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    // 📌 Pobranie wiadomości dla danego dokumentu
    #[Route('/{documentId}', name: 'api_get_conversations', methods: ['GET'])]
    public function getConversations(int $documentId): JsonResponse
    {
        $conversations = $this->entityManager->getRepository(Conversations::class)
            ->findBy(['document' => $documentId], ['date' => 'ASC']);

        $data = array_map(fn($msg) => [
            'id' => $msg->getId(),
            'content' => $msg->getContent(),
            'user_id' => $msg->getUser()->getId(),
            'date' => $msg->getDate()->format('Y-m-d H:i:s'),
        ], $conversations);

        return new JsonResponse($data);
    }

    // 📌 Wysyłanie wiadomości
    #[Route('/send', name: 'api_send_conversation', methods: ['POST'])]
    public function sendMessage(Request $request): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Nie jesteś zalogowany.'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);
        if (empty($data['document_id']) || empty($data['content'])) {
            return new JsonResponse(['error' => 'Nieprawidłowe dane.'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $document = $this->entityManager->getRepository(Documents::class)->find($data['document_id']);
        if (!$document) {
            return new JsonResponse(['error' => 'Dokument nie istnieje.'], JsonResponse::HTTP_NOT_FOUND);
        }

        // 📌 Pobranie liczby istniejących wiadomości dla danego dokumentu
        $existingMessages = $this->entityManager->getRepository(Conversations::class)->count(['document' => $document]);
        $orderNum = $existingMessages + 1;

        // 📌 Tworzenie nowej wiadomości
        $message = new Conversations();
        $message->setDocument($document);
        $message->setUser($user);
        $message->setContent($data['content']);
        $message->setOrderNum($orderNum);

        $this->entityManager->persist($message);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Wiadomość wysłana.', 'order' => $orderNum]);
    }
}
