<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity]
#[ORM\Table(name: "users")]
class User implements UserInterface, PasswordAuthenticatedUserInterface // 🟢 Dodano nowy interfejs
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private int $id;

    #[ORM\Column(type: "string", length: 255)]
    private string $imie;

    #[ORM\Column(type: "string", length: 255)]
    private string $nazwisko;

    #[ORM\Column(type: "string", length: 255, unique: true)]
    private string $login;

    #[ORM\Column(name: "haslo", type: "string", length: 255)] // 🟢 Mapa kolumny "haslo"
    private string $password;

    #[ORM\Column(type: "json")]
    private array $roles = [];

    public function getId(): int
    {
        return $this->id;
    }

    public function getLogin(): string
    {
        return $this->login;
    }

    public function getUserIdentifier(): string
    {
        return $this->login;
    }

    // 🟢 Metoda wymagana przez PasswordAuthenticatedUserInterface
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function getRoles(): array
    {
        // Jeśli brak ról, domyślnie przypisz ROLE_USER
        return !empty($this->roles) ? $this->roles : ['ROLE_USER'];
    }

    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    public function eraseCredentials(): void
    {
        // Jeśli przechowujesz dane wrażliwe w pamięci, usuń je tutaj
    }
}
