<?php

namespace App\Controller;

use App\Entity\Conversations;
use App\Entity\Documents;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;

#[Route('/api/conversations')]
class ConversationsController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @OA\Get(
     *     path="/api/conversations/{documentId}",
     *     summary="Get conversations for a specific document",
     *     description="Retrieve all conversations related to a specific document, ordered by date.",
     *     @OA\Parameter(
     *         name="documentId",
     *         in="path",
     *         description="The ID of the document to get conversations for",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of conversations",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="content", type="string"),
     *                 @OA\Property(property="user_id", type="integer"),
     *                 @OA\Property(property="date", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Document not found"
     *     )
     * )
     */
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

    /**
     * @OA\Post(
     *     path="/api/conversations/send",
     *     summary="Send a new conversation message",
     *     description="Send a message related to a specific document.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"document_id", "content"},
     *             @OA\Property(property="document_id", type="integer", description="The ID of the document"),
     *             @OA\Property(property="content", type="string", description="The content of the message")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Message sent successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="order", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input data"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - User not logged in"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Document not found"
     *     )
     * )
     */
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

        $existingMessages = $this->entityManager->getRepository(Conversations::class)->count(['document' => $document]);
        $orderNum = $existingMessages + 1;

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
