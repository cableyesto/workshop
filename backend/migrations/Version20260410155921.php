<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260410155921 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE garage_employee (garage_id INT NOT NULL, employee_id INT NOT NULL, PRIMARY KEY (garage_id, employee_id))');
        $this->addSql('CREATE INDEX IDX_AD16A609C4FFF555 ON garage_employee (garage_id)');
        $this->addSql('CREATE INDEX IDX_AD16A6098C03F15C ON garage_employee (employee_id)');
        $this->addSql('ALTER TABLE garage_employee ADD CONSTRAINT FK_AD16A609C4FFF555 FOREIGN KEY (garage_id) REFERENCES garage (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE garage_employee ADD CONSTRAINT FK_AD16A6098C03F15C FOREIGN KEY (employee_id) REFERENCES employee (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE garage_employee DROP CONSTRAINT FK_AD16A609C4FFF555');
        $this->addSql('ALTER TABLE garage_employee DROP CONSTRAINT FK_AD16A6098C03F15C');
        $this->addSql('DROP TABLE garage_employee');
    }
}
