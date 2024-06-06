<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240606132130 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sortie ADD groupe_prive_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sortie ADD CONSTRAINT FK_3C3FD3F2EFB6D465 FOREIGN KEY (groupe_prive_id) REFERENCES groupe_prive (id)');
        $this->addSql('CREATE INDEX IDX_3C3FD3F2EFB6D465 ON sortie (groupe_prive_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sortie DROP FOREIGN KEY FK_3C3FD3F2EFB6D465');
        $this->addSql('DROP INDEX IDX_3C3FD3F2EFB6D465 ON sortie');
        $this->addSql('ALTER TABLE sortie DROP groupe_prive_id');
    }
}
