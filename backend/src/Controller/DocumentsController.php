<?php

namespace App\Controller;

use App\Entity\Documents;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/documents')]
class DocumentsController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Pobieranie prac zalogowanego użytkownika
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
     * Dodawanie nowej pracy
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
        $document->setStatus(4); // Domyślny status: "w trakcie"

        // Przypisanie promotora (jeśli podano)
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
        // error_log($user->getId());
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

    // 📌 Pobieranie szczegółów dokumentu
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
