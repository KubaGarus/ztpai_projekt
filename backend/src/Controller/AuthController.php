<?php
namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api')]
class AuthController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;
    private JWTTokenManagerInterface $jwtManager;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, JWTTokenManagerInterface $jwtManager)
    {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
        $this->jwtManager = $jwtManager;
    }

    #[Route('/login', name: 'api_login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
      
        // Pobranie użytkownika z bazy
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['login' => $data['login']]);
        
        // Sprawdzenie, czy użytkownik istnieje i hasło jest poprawne
        if (!$user || !$this->passwordHasher->isPasswordValid($user, $data['password'])) {
            return new JsonResponse(['error' => 'Invalid credentials.'], JsonResponse::HTTP_UNAUTHORIZED);
        }
    
        error_log("Użytkownik: " . ($user ? "TAK" : "NIE"));
        try {
            $token = $this->jwtManager->create($user);
            return new JsonResponse(['token' => $token]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Błąd JWT!',
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
        
        // Zwrot tokena JWT
        // return new JsonResponse(['token' => $this->jwtManager->create($user)]);
    }
    

}
