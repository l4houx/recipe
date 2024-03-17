<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240314004255 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE help_center_article (id INT AUTO_INCREMENT NOT NULL, category_id INT NOT NULL, tags VARCHAR(150) DEFAULT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, views INT DEFAULT NULL, is_online TINYINT(1) DEFAULT 0 NOT NULL, is_featured TINYINT(1) DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_CEAD054512469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE help_center_category (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, color VARCHAR(255) DEFAULT \'#5dade2\', icon VARCHAR(50) DEFAULT NULL, is_online TINYINT(1) DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_A4E7FFB8727ACA70 (parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE help_center_article ADD CONSTRAINT FK_CEAD054512469DE2 FOREIGN KEY (category_id) REFERENCES help_center_category (id)');
        $this->addSql('ALTER TABLE help_center_category ADD CONSTRAINT FK_A4E7FFB8727ACA70 FOREIGN KEY (parent_id) REFERENCES help_center_category (id)');
        $this->addSql('ALTER TABLE user ADD theme VARCHAR(255) DEFAULT NULL, ADD locale VARCHAR(2) DEFAULT \'fr\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE help_center_article DROP FOREIGN KEY FK_CEAD054512469DE2');
        $this->addSql('ALTER TABLE help_center_category DROP FOREIGN KEY FK_A4E7FFB8727ACA70');
        $this->addSql('DROP TABLE help_center_article');
        $this->addSql('DROP TABLE help_center_category');
        $this->addSql('ALTER TABLE user DROP theme, DROP locale');
    }
}
