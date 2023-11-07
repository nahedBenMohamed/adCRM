<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231107130920 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE formation (id INT AUTO_INCREMENT NOT NULL, formateur_id INT DEFAULT NULL, nom_formation VARCHAR(255) NOT NULL, duree_formation VARCHAR(255) NOT NULL, domaine_formation VARCHAR(255) NOT NULL, lien_formation VARCHAR(255) DEFAULT NULL, adresse_formation VARCHAR(255) DEFAULT NULL, pdf_formation VARCHAR(255) DEFAULT NULL, date_debut_formation DATETIME DEFAULT NULL, date_fin_formation DATETIME DEFAULT NULL, modalite_formation VARCHAR(255) DEFAULT NULL, INDEX IDX_404021BF155D8F51 (formateur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE formation_user (id INT AUTO_INCREMENT NOT NULL, formation_id INT DEFAULT NULL, user_id INT DEFAULT NULL, INDEX IDX_DA4C33095200282E (formation_id), INDEX IDX_DA4C3309A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE trainee (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, password VARCHAR(255) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', UNIQUE INDEX UNIQ_46C68DE7E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE trainee_formation (trainee_id INT NOT NULL, formation_id INT NOT NULL, INDEX IDX_D4EA7F4136C682D0 (trainee_id), INDEX IDX_D4EA7F415200282E (formation_id), PRIMARY KEY(trainee_id, formation_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE formation ADD CONSTRAINT FK_404021BF155D8F51 FOREIGN KEY (formateur_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE formation_user ADD CONSTRAINT FK_DA4C33095200282E FOREIGN KEY (formation_id) REFERENCES formation (id)');
        $this->addSql('ALTER TABLE formation_user ADD CONSTRAINT FK_DA4C3309A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE trainee_formation ADD CONSTRAINT FK_D4EA7F4136C682D0 FOREIGN KEY (trainee_id) REFERENCES trainee (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE trainee_formation ADD CONSTRAINT FK_D4EA7F415200282E FOREIGN KEY (formation_id) REFERENCES formation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user CHANGE roles roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE formation DROP FOREIGN KEY FK_404021BF155D8F51');
        $this->addSql('ALTER TABLE formation_user DROP FOREIGN KEY FK_DA4C33095200282E');
        $this->addSql('ALTER TABLE formation_user DROP FOREIGN KEY FK_DA4C3309A76ED395');
        $this->addSql('ALTER TABLE trainee_formation DROP FOREIGN KEY FK_D4EA7F4136C682D0');
        $this->addSql('ALTER TABLE trainee_formation DROP FOREIGN KEY FK_D4EA7F415200282E');
        $this->addSql('DROP TABLE formation');
        $this->addSql('DROP TABLE formation_user');
        $this->addSql('DROP TABLE trainee');
        $this->addSql('DROP TABLE trainee_formation');
        $this->addSql('ALTER TABLE user CHANGE roles roles LONGTEXT NOT NULL COLLATE `utf8mb4_bin`');
    }
}
