<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240310173240 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE app_layout_setting (id INT AUTO_INCREMENT NOT NULL, logo_name VARCHAR(255) DEFAULT NULL, favicon_name VARCHAR(255) DEFAULT NULL, og_image_name VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE currency (id INT AUTO_INCREMENT NOT NULL, ccy VARCHAR(3) NOT NULL, symbol VARCHAR(50) DEFAULT NULL, UNIQUE INDEX UNIQ_6956883FD2D95D97 (ccy), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE homepage_hero_setting (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(100) DEFAULT NULL, paragraph LONGTEXT DEFAULT NULL, content LONGTEXT NOT NULL, custom_background_name VARCHAR(50) DEFAULT NULL, show_search_box TINYINT(1) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE menu (id INT AUTO_INCREMENT NOT NULL, header VARCHAR(128) DEFAULT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE menu_element (id INT AUTO_INCREMENT NOT NULL, menu_id INT DEFAULT NULL, link VARCHAR(255) DEFAULT NULL, custom_link VARCHAR(255) DEFAULT NULL, position INT NOT NULL, label VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, icon VARCHAR(50) DEFAULT NULL, INDEX IDX_C99B4387CCD7E912 (menu_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE setting (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, value LONGTEXT DEFAULT NULL, type VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_9F74B8985E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE menu_element ADD CONSTRAINT FK_C99B4387CCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id)');
        $this->addSql('ALTER TABLE recipe ADD isonhomepageslider_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE recipe ADD CONSTRAINT FK_DA88B137376C51EF FOREIGN KEY (isonhomepageslider_id) REFERENCES homepage_hero_setting (id)');
        $this->addSql('CREATE INDEX IDX_DA88B137376C51EF ON recipe (isonhomepageslider_id)');
        $this->addSql('ALTER TABLE user ADD isuseronhomepageslider_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649FF1B2680 FOREIGN KEY (isuseronhomepageslider_id) REFERENCES homepage_hero_setting (id)');
        $this->addSql('CREATE INDEX IDX_8D93D649FF1B2680 ON user (isuseronhomepageslider_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE recipe DROP FOREIGN KEY FK_DA88B137376C51EF');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649FF1B2680');
        $this->addSql('ALTER TABLE menu_element DROP FOREIGN KEY FK_C99B4387CCD7E912');
        $this->addSql('DROP TABLE app_layout_setting');
        $this->addSql('DROP TABLE currency');
        $this->addSql('DROP TABLE homepage_hero_setting');
        $this->addSql('DROP TABLE menu');
        $this->addSql('DROP TABLE menu_element');
        $this->addSql('DROP TABLE setting');
        $this->addSql('DROP INDEX IDX_DA88B137376C51EF ON recipe');
        $this->addSql('ALTER TABLE recipe DROP isonhomepageslider_id');
        $this->addSql('DROP INDEX IDX_8D93D649FF1B2680 ON user');
        $this->addSql('ALTER TABLE user DROP isuseronhomepageslider_id');
    }
}
