<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260414200823 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE intervention ADD car_id INT NOT NULL');
        $this->addSql('ALTER TABLE intervention ADD CONSTRAINT FK_D11814ABC3C6F69F FOREIGN KEY (car_id) REFERENCES car (id) NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_D11814ABC3C6F69F ON intervention (car_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE intervention DROP CONSTRAINT FK_D11814ABC3C6F69F');
        $this->addSql('DROP INDEX IDX_D11814ABC3C6F69F');
        $this->addSql('ALTER TABLE intervention DROP car_id');
    }
}
