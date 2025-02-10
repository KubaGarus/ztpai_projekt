<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250207125700 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Dodanie domyślnych użytkowników do tabeli users';
    }

    public function up(Schema $schema): void
    {
        // Wstawianie danych do tabeli users
        $this->addSql("
            INSERT INTO users (imie, nazwisko, login, haslo, roles) VALUES
            ('Admin', 'Adminowski', 'admin', '" . password_hash('admin123', PASSWORD_BCRYPT) . "', '[\"ROLE_ADMIN\"]'),
            ('Jan', 'Kowalski', 'jkowalski', '" . password_hash('haslo123', PASSWORD_BCRYPT) . "', '[\"ROLE_USER\"]')
        ");
    }

    public function down(Schema $schema): void
    {
        // Usuwanie danych przy rollbacku migracji
        $this->addSql("DELETE FROM users WHERE login IN ('admin', 'jkowalski')");
    }
}
