<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231107132456 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE formation_user DROP FOREIGN KEY FK_DA4C3309A76ED395');
        $this->addSql('DROP INDEX IDX_DA4C3309A76ED395 ON formation_user');
        $this->addSql('ALTER TABLE formation_user CHANGE user_id trainee_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE formation_user ADD CONSTRAINT FK_DA4C330936C682D0 FOREIGN KEY (trainee_id) REFERENCES trainee (id)');
        $this->addSql('CREATE INDEX IDX_DA4C330936C682D0 ON formation_user (trainee_id)');
        $this->addSql('ALTER TABLE trainee ADD first_name VARCHAR(70) DEFAULT NULL, ADD last_name VARCHAR(70) DEFAULT NULL, ADD phone LONGTEXT DEFAULT NULL, DROP password, DROP roles');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE formation_user DROP FOREIGN KEY FK_DA4C330936C682D0');
        $this->addSql('DROP INDEX IDX_DA4C330936C682D0 ON formation_user');
        $this->addSql('ALTER TABLE formation_user CHANGE trainee_id user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE formation_user ADD CONSTRAINT FK_DA4C3309A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_DA4C3309A76ED395 ON formation_user (user_id)');
        $this->addSql('ALTER TABLE trainee ADD password VARCHAR(255) NOT NULL, ADD roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', DROP first_name, DROP last_name, DROP phone');
    }
}
