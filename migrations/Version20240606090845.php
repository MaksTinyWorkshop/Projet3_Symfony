<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240606090845 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE groupe_prive (id INT AUTO_INCREMENT NOT NULL, createur_id INT NOT NULL, nom VARCHAR(255) NOT NULL, INDEX IDX_A8D00A9D73A201E5 (createur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE groupe_prive_participant (groupe_prive_id INT NOT NULL, participants_id INT NOT NULL, INDEX IDX_9990AD24EFB6D465 (groupe_prive_id), INDEX IDX_9990AD24838709D5 (participants_id), PRIMARY KEY(groupe_prive_id, participants_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE groupe_prive ADD CONSTRAINT FK_A8D00A9D73A201E5 FOREIGN KEY (createur_id) REFERENCES participants (id)');
        $this->addSql('ALTER TABLE groupe_prive_participant ADD CONSTRAINT FK_9990AD24EFB6D465 FOREIGN KEY (groupe_prive_id) REFERENCES groupe_prive (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE groupe_prive_participant ADD CONSTRAINT FK_9990AD24838709D5 FOREIGN KEY (participants_id) REFERENCES participants (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sortie CHANGE organisateur_id organisateur_id INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE groupe_prive DROP FOREIGN KEY FK_A8D00A9D73A201E5');
        $this->addSql('ALTER TABLE groupe_prive_participant DROP FOREIGN KEY FK_9990AD24EFB6D465');
        $this->addSql('ALTER TABLE groupe_prive_participant DROP FOREIGN KEY FK_9990AD24838709D5');
        $this->addSql('DROP TABLE groupe_prive');
        $this->addSql('DROP TABLE groupe_prive_participant');
        $this->addSql('ALTER TABLE sortie CHANGE organisateur_id organisateur_id INT NOT NULL');
    }
}
