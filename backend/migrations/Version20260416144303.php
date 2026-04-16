<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260416144303 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE mechanic_intervention (mechanic_id INT NOT NULL, intervention_id INT NOT NULL, PRIMARY KEY (mechanic_id, intervention_id))');
        $this->addSql('CREATE INDEX IDX_1B7556A59A67DB00 ON mechanic_intervention (mechanic_id)');
        $this->addSql('CREATE INDEX IDX_1B7556A58EAE3863 ON mechanic_intervention (intervention_id)');
        $this->addSql('ALTER TABLE mechanic_intervention ADD CONSTRAINT FK_1B7556A59A67DB00 FOREIGN KEY (mechanic_id) REFERENCES mechanic (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE mechanic_intervention ADD CONSTRAINT FK_1B7556A58EAE3863 FOREIGN KEY (intervention_id) REFERENCES intervention (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE mechanic_intervention DROP CONSTRAINT FK_1B7556A59A67DB00');
        $this->addSql('ALTER TABLE mechanic_intervention DROP CONSTRAINT FK_1B7556A58EAE3863');
        $this->addSql('DROP TABLE mechanic_intervention');
    }
}
