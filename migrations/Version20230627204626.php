<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230627204626 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE formation_stagiaire (formation_id INT NOT NULL, stagiaire_id INT NOT NULL, INDEX IDX_851FA7EC5200282E (formation_id), INDEX IDX_851FA7ECBBA93DD6 (stagiaire_id), PRIMARY KEY(formation_id, stagiaire_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE formation_stagiaire ADD CONSTRAINT FK_851FA7EC5200282E FOREIGN KEY (formation_id) REFERENCES formation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE formation_stagiaire ADD CONSTRAINT FK_851FA7ECBBA93DD6 FOREIGN KEY (stagiaire_id) REFERENCES stagiaire (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE formation_stagiaire DROP FOREIGN KEY FK_851FA7EC5200282E');
        $this->addSql('ALTER TABLE formation_stagiaire DROP FOREIGN KEY FK_851FA7ECBBA93DD6');
        $this->addSql('DROP TABLE formation_stagiaire');
    }
}
