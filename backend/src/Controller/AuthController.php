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
      
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['login' => $data['login']]);
        
        if (!$user || !$this->passwordHasher->isPasswordValid($user, $data['password'])) {
            return new JsonResponse(['error' => 'Invalid credentials.'], JsonResponse::HTTP_UNAUTHORIZED);
        }
    
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
    }
    

    #[Route('/register', name: 'api_register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
    
        if (empty($data['imie']) || empty($data['nazwisko']) || empty($data['login']) || empty($data['password'])) {
            return new JsonResponse(['error' => 'Wszystkie pola są wymagane.'], JsonResponse::HTTP_BAD_REQUEST);
        }
    
        if (strlen($data['login']) < 4) {
            return new JsonResponse(['error' => 'Login musi mieć co najmniej 4 znaki.'], JsonResponse::HTTP_BAD_REQUEST);
        }
    
        if (strlen($data['password']) < 6) {
            return new JsonResponse(['error' => 'Hasło musi mieć co najmniej 6 znaków.'], JsonResponse::HTTP_BAD_REQUEST);
        }
    
        $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['login' => $data['login']]);
        if ($existingUser) {
            return new JsonResponse(['error' => 'Użytkownik o takim loginie już istnieje.'], JsonResponse::HTTP_BAD_REQUEST);
        }
    
        $user = new User();
        $user->setImie($data['imie']);
        $user->setNazwisko($data['nazwisko']);
        $user->setLogin($data['login']);
        $user->setPassword($this->passwordHasher->hashPassword($user, $data['password']));
        $user->setRoles($data['roles']);
    
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    
        return new JsonResponse(['message' => 'Rejestracja zakończona sukcesem.']);
    }
    
    #[Route('/dashboard', name: 'api_dashboard', methods: ['GET'])]
    public function dashboard(): JsonResponse
    {
        $user = $this->getUser();
    
        if (!$user) {
            return new JsonResponse(['error' => 'Nie jesteś zalogowany.'], JsonResponse::HTTP_UNAUTHORIZED);
        }
    
        return new JsonResponse([
            'message' => 'Witaj w panelu głównym!',
            'user' => [
                'id' => $user->getId(),
                'imie' => $user->getImie(),
                'nazwisko' => $user->getNazwisko(),
                'login' => $user->getLogin(),
                'roles' => $user->getRoles(),
            ]
        ]);
    }    
}
