<?php
namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(name="Admin")
 */
#[Route('/api/users')]
class UserController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @OA\Get(
     *     path="/api/users",
     *     summary="Pobiera listę wszystkich użytkowników",
     *     security={{ "bearerAuth":{} }},
     *     @OA\Response(response=200, description="Lista użytkowników")
     * )
     */
    #[Route('', name: 'api_users_list', methods: ['GET'])]
    public function listUsers(): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN'); // ✅ Ochrona tylko dla administratorów

        $users = $this->entityManager->getRepository(User::class)->findAll();
        $data = array_map(fn($user) => [
            'id' => $user->getId(),
            'login' => $user->getLogin(),
            'imie' => $user->getImie(),
            'nazwisko' => $user->getNazwisko(),
            'roles' => $user->getRoles(),
        ], $users);

        return new JsonResponse($data);
    }

    /**
     * @OA\Delete(
     *     path="/api/users/{id}",
     *     summary="Usuwa użytkownika",
     *     security={{ "bearerAuth":{} }},
     *     @OA\Parameter(name="id", in="path", required=true, description="ID użytkownika"),
     *     @OA\Response(response=204, description="Użytkownik usunięty"),
     *     @OA\Response(response=403, description="Brak dostępu"),
     * )
     */
    #[Route('/{id}', name: 'api_users_delete', methods: ['DELETE'])]
    public function deleteUser(int $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $user = $this->entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            return new JsonResponse(['error' => 'Użytkownik nie istnieje.'], Response::HTTP_NOT_FOUND);
        }

        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
