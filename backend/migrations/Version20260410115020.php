<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260410115020 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE owner_garage (owner_id INT NOT NULL, garage_id INT NOT NULL, PRIMARY KEY (owner_id, garage_id))');
        $this->addSql('CREATE INDEX IDX_3ADC4D557E3C61F9 ON owner_garage (owner_id)');
        $this->addSql('CREATE INDEX IDX_3ADC4D55C4FFF555 ON owner_garage (garage_id)');
        $this->addSql('ALTER TABLE owner_garage ADD CONSTRAINT FK_3ADC4D557E3C61F9 FOREIGN KEY (owner_id) REFERENCES owner (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE owner_garage ADD CONSTRAINT FK_3ADC4D55C4FFF555 FOREIGN KEY (garage_id) REFERENCES garage (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE owner_garage DROP CONSTRAINT FK_3ADC4D557E3C61F9');
        $this->addSql('ALTER TABLE owner_garage DROP CONSTRAINT FK_3ADC4D55C4FFF555');
        $this->addSql('DROP TABLE owner_garage');
    }
}
