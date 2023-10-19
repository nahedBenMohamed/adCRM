<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230629120723 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE formation_stagiaire DROP FOREIGN KEY FK_851FA7ECBBA93DD6');
        $this->addSql('ALTER TABLE formation_stagiaire DROP FOREIGN KEY FK_851FA7EC5200282E');
        $this->addSql('ALTER TABLE formation_stagiaire ADD CONSTRAINT FK_851FA7ECBBA93DD6 FOREIGN KEY (stagiaire_id) REFERENCES stagiaire (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE formation_stagiaire ADD CONSTRAINT FK_851FA7EC5200282E FOREIGN KEY (formation_id) REFERENCES formation (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE formation_stagiaire DROP FOREIGN KEY FK_851FA7EC5200282E');
        $this->addSql('ALTER TABLE formation_stagiaire DROP FOREIGN KEY FK_851FA7ECBBA93DD6');
        $this->addSql('ALTER TABLE formation_stagiaire ADD CONSTRAINT FK_851FA7EC5200282E FOREIGN KEY (formation_id) REFERENCES formation (id)');
        $this->addSql('ALTER TABLE formation_stagiaire ADD CONSTRAINT FK_851FA7ECBBA93DD6 FOREIGN KEY (stagiaire_id) REFERENCES stagiaire (id)');
    }
}
