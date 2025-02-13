<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250213154057 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE conversations (conversation_id INT GENERATED BY DEFAULT AS IDENTITY NOT NULL, order_num INT NOT NULL, content TEXT NOT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, document_id INT DEFAULT NULL, user_id INT DEFAULT NULL, PRIMARY KEY(conversation_id))');
        $this->addSql('CREATE INDEX IDX_C2521BF1C33F7837 ON conversations (document_id)');
        $this->addSql('CREATE INDEX IDX_C2521BF1A76ED395 ON conversations (user_id)');
        $this->addSql('CREATE TABLE documents (document_id INT GENERATED BY DEFAULT AS IDENTITY NOT NULL, title VARCHAR(255) NOT NULL, content TEXT NOT NULL, status SMALLINT NOT NULL, upload_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, user_id INT DEFAULT NULL, promotor_id INT DEFAULT NULL, PRIMARY KEY(document_id))');
        $this->addSql('CREATE INDEX IDX_A2B07288A76ED395 ON documents (user_id)');
        $this->addSql('CREATE INDEX IDX_A2B07288134AAD7 ON documents (promotor_id)');
        $this->addSql('CREATE TABLE logs (log_id INT GENERATED BY DEFAULT AS IDENTITY NOT NULL, status_before SMALLINT NOT NULL, status_after SMALLINT NOT NULL, upload_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, document_id INT DEFAULT NULL, user_id INT DEFAULT NULL, PRIMARY KEY(log_id))');
        $this->addSql('CREATE INDEX IDX_F08FC65CC33F7837 ON logs (document_id)');
        $this->addSql('CREATE INDEX IDX_F08FC65CA76ED395 ON logs (user_id)');
        $this->addSql('ALTER TABLE conversations ADD CONSTRAINT FK_C2521BF1C33F7837 FOREIGN KEY (document_id) REFERENCES documents (document_id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE conversations ADD CONSTRAINT FK_C2521BF1A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE documents ADD CONSTRAINT FK_A2B07288A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE documents ADD CONSTRAINT FK_A2B07288134AAD7 FOREIGN KEY (promotor_id) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE logs ADD CONSTRAINT FK_F08FC65CC33F7837 FOREIGN KEY (document_id) REFERENCES documents (document_id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE logs ADD CONSTRAINT FK_F08FC65CA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE users ALTER id TYPE INT');
        $this->addSql('ALTER TABLE users ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE users ALTER id ADD GENERATED BY DEFAULT AS IDENTITY');
        $this->addSql('ALTER INDEX users_login_key RENAME TO UNIQ_1483A5E9AA08CB10');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE conversations DROP CONSTRAINT FK_C2521BF1C33F7837');
        $this->addSql('ALTER TABLE conversations DROP CONSTRAINT FK_C2521BF1A76ED395');
        $this->addSql('ALTER TABLE documents DROP CONSTRAINT FK_A2B07288A76ED395');
        $this->addSql('ALTER TABLE documents DROP CONSTRAINT FK_A2B07288134AAD7');
        $this->addSql('ALTER TABLE logs DROP CONSTRAINT FK_F08FC65CC33F7837');
        $this->addSql('ALTER TABLE logs DROP CONSTRAINT FK_F08FC65CA76ED395');
        $this->addSql('DROP TABLE conversations');
        $this->addSql('DROP TABLE documents');
        $this->addSql('DROP TABLE logs');
        $this->addSql('ALTER TABLE users ALTER id TYPE INT');
        $this->addSql('ALTER TABLE users ALTER id SET DEFAULT users_id_seq');
        $this->addSql('ALTER TABLE users ALTER id DROP IDENTITY');
        $this->addSql('ALTER INDEX uniq_1483a5e9aa08cb10 RENAME TO users_login_key');
    }
}
