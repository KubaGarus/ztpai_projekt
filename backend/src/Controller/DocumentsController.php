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
}
