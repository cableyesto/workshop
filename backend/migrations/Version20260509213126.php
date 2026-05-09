<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260509213126 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client ADD garage_id INT NOT NULL');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_C7440455C4FFF555 FOREIGN KEY (garage_id) REFERENCES garage (id) NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_C7440455C4FFF555 ON client (garage_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE client DROP CONSTRAINT FK_C7440455C4FFF555');
        $this->addSql('DROP INDEX IDX_C7440455C4FFF555');
        $this->addSql('ALTER TABLE client DROP garage_id');
    }
}
