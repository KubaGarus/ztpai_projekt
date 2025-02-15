<?php

namespace App\Controller;

use App\Entity\Documents;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;

#[Route('/api/documents')]
class DocumentsController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @OA\Get(
     *     path="/api/documents/my",
     *     summary="Get documents for logged in user",
     *     description="Retrieve all documents uploaded by the logged in user.",
     *     @OA\Response(
     *         response=200,
     *         description="List of documents",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="title", type="string"),
     *                 @OA\Property(property="promotor", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - User not logged in"
     *     )
     * )
     */
    #[Route('/my', name: 'api_my_documents', methods: ['GET'])]
    public function getUserDocuments(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Nie jesteś zalogowany.'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $documents = $this->entityManager->getRepository(Documents::class)
            ->findBy(['user' => $user]);

        if (empty($documents)) {
            return new JsonResponse(['message' => 'Nie posiadasz żadnych prac.'], JsonResponse::HTTP_OK);
        }

        $data = array_map(fn($doc) => [
            'id' => $doc->getId(),
            'title' => $doc->getTitle(),
            'promotor' => $doc->getPromotor() ? $doc->getPromotor()->getImie() . ' ' . $doc->getPromotor()->getNazwisko() : 'Brak promotora'
        ], $documents);

        return new JsonResponse($data);
    }

    /**
     * @OA\Post(
     *     path="/api/documents/create",
     *     summary="Create a new document",
     *     description="Create a new document for the logged in user.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title"},
     *             @OA\Property(property="title", type="string", description="Title of the document"),
     *             @OA\Property(property="content", type="string", description="Content of the document"),
     *             @OA\Property(property="promotor_id", type="integer", description="ID of the promotor")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Document created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request - Missing required fields"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - User not logged in"
     *     )
     * )
     */
    #[Route('/create', name: 'api_create_document', methods: ['POST'])]
    public function createDocument(Request $request): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Nie jesteś zalogowany.'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);
        if (empty($data['title'])) {
            return new JsonResponse(['error' => 'Tytuł jest wymagany.'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $document = new Documents();
        $document->setTitle($data['title']);
        $document->setContent($data['content'] ?? '');
        $document->setUser($user);
        $document->setStatus(4);

        if (!empty($data['promotor_id'])) {
            $promotor = $this->entityManager->getRepository(User::class)->find($data['promotor_id']);
            if ($promotor) {
                $document->setPromotor($promotor);
            }
        }

        $this->entityManager->persist($document);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Praca dodana pomyślnie.']);
    }

    /**
     * @OA\Get(
     *     path="/api/documents/promoted",
     *     summary="Get promoted documents for the logged in promotor",
     *     description="Retrieve documents that are promoted by the logged in user.",
     *     @OA\Response(
     *         response=200,
     *         description="List of promoted documents",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="title", type="string"),
     *                 @OA\Property(property="content", type="string"),
     *                 @OA\Property(property="status", type="integer"),
     *                 @OA\Property(property="upload_date", type="string", format="date-time"),
     *                 @OA\Property(property="student", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - User not logged in"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - User does not have sufficient permissions"
     *     )
     * )
     */
    #[Route('/promoted', name: 'api_documents_promoted', methods: ['GET'])]
    public function getPromotedDocuments(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['error' => 'Nie jesteś zalogowany.'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        if (!in_array('ROLE_PROMOTOR', $user->getRoles()) && !in_array('ROLE_ADMIN', $user->getRoles())) {
            return new JsonResponse(['error' => 'Brak dostępu.'], JsonResponse::HTTP_FORBIDDEN);
        }

        $documents = $this->entityManager->getRepository(Documents::class)->findBy(['promotor' => $user]);

        $data = array_map(function ($doc) {
            return [
                'id' => $doc->getId(),
                'title' => $doc->getTitle(),
                'content' => $doc->getContent(),
                'status' => $doc->getStatus(),
                'upload_date' => $doc->getUploadDate()->format('Y-m-d H:i:s'),
                'student' => $doc->getUser()->getImie() . ' ' . $doc->getUser()->getNazwisko(),
            ];
        }, $documents);

        return new JsonResponse($data);
    }

    /**
     * @OA\Get(
     *     path="/api/documents/{id}",
     *     summary="Get details of a specific document",
     *     description="Retrieve the details of a specific document by its ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="The ID of the document to get details for",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Document details",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="content", type="string"),
     *             @OA\Property(property="status", type="integer"),
     *             @OA\Property(property="upload_date", type="string", format="date-time")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Document not found"
     *     )
     * )
     */
    #[Route('/{id}', name: 'api_document_details', methods: ['GET'])]
    public function getDocumentDetails(int $id): JsonResponse
    {
        $document = $this->entityManager->getRepository(Documents::class)->find($id);

        if (!$document) {
            return new JsonResponse(['error' => 'Dokument nie istnieje.'], JsonResponse::HTTP_NOT_FOUND);
        }

        return new JsonResponse([ 
            'id' => $document->getId(),
            'title' => $document->getTitle(),
            'content' => $document->getContent(),
            'status' => $document->getStatus(),
            'upload_date' => $document->getUploadDate()->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * @OA\Patch(
     *     path="/api/documents/{id}/status",
     *     summary="Update the status of a document",
     *     description="Update the status of a specific document.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="The ID of the document to update",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="integer", description="The new status of the document")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Document status updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request - Missing required fields"
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
    #[Route('/{id}/status', name: 'api_update_document_status', methods: ['PATCH'])]
    public function updateDocumentStatus(int $id, Request $request): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Nie jesteś zalogowany.'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $document = $this->entityManager->getRepository(Documents::class)->find($id);
        if (!$document) {
            return new JsonResponse(['error' => 'Dokument nie istnieje.'], JsonResponse::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        if (!isset($data['status'])) {
            return new JsonResponse(['error' => 'Nie podano statusu.'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $document->setStatus($data['status']);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Status zaktualizowany pomyślnie.']);
    }
}
