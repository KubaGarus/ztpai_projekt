<?php
namespace App\Controller;

use OpenApi\Annotations as OA;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @OA\Tag(name="Authentication")
 * @OA\SecurityScheme(
 *      securityScheme="bearerAuth",
 *      type="http",
 *      scheme="bearer",
 *      bearerFormat="JWT"
 * )
 */
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

    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Logowanie użytkownika",
     *     requestBody=@OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="login", type="string"),
     *             @OA\Property(property="password", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Zwraca token JWT"),
     *     @OA\Response(response=401, description="Błędne dane logowania")
     * )
     */
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
            return new JsonResponse(['error' => 'Błąd JWT!'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Rejestracja nowego użytkownika",
     *     requestBody=@OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="imie", type="string"),
     *             @OA\Property(property="nazwisko", type="string"),
     *             @OA\Property(property="login", type="string"),
     *             @OA\Property(property="password", type="string"),
     *             @OA\Property(property="roles", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     *     @OA\Response(response=201, description="Użytkownik zarejestrowany pomyślnie"),
     *     @OA\Response(response=400, description="Niepoprawne dane rejestracyjne")
     * )
     */
    #[Route('/register', name: 'api_register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['imie']) || empty($data['nazwisko']) || empty($data['login']) || empty($data['password'])) {
            return new JsonResponse(['error' => 'Wszystkie pola są wymagane.'], JsonResponse::HTTP_BAD_REQUEST);
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

        return new JsonResponse(['message' => 'Rejestracja zakończona sukcesem.'], JsonResponse::HTTP_CREATED);
    }

    /**
     * @OA\Get(
     *     path="/api/dashboard",
     *     summary="Pobiera dane zalogowanego użytkownika",
     *     security={{ "bearerAuth":{} }},
     *     @OA\Response(response=200, description="Zwraca dane użytkownika"),
     *     @OA\Response(response=401, description="Brak autoryzacji")
     * )
     */
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
