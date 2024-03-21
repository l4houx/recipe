<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240321204112 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE attachment (id INT AUTO_INCREMENT NOT NULL, attachment_name VARCHAR(255) NOT NULL, attachment_size INT UNSIGNED NOT NULL, deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE contact_request (id INT AUTO_INCREMENT NOT NULL, ip VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE content (id INT AUTO_INCREMENT NOT NULL, attachment_id INT DEFAULT NULL, user_id INT DEFAULT NULL, title VARCHAR(128) NOT NULL, slug VARCHAR(128) NOT NULL, content LONGTEXT NOT NULL, readtime INT DEFAULT NULL, reference VARCHAR(255) NOT NULL, status INT DEFAULT 0 NOT NULL, enablereviews TINYINT(1) DEFAULT 1 NOT NULL, published_at DATETIME DEFAULT NULL, is_online TINYINT(1) DEFAULT 0 NOT NULL, views INT DEFAULT NULL, deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, type VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_FEC530A9989D9B62 (slug), UNIQUE INDEX UNIQ_FEC530A9AEA34913 (reference), INDEX IDX_FEC530A9464E68B (attachment_id), INDEX IDX_FEC530A9A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE revise (id INT AUTO_INCREMENT NOT NULL, author_id INT NOT NULL, target_id INT NOT NULL, status INT DEFAULT 0 NOT NULL, content LONGTEXT NOT NULL, comment VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_96624559F675F31B (author_id), INDEX IDX_96624559158E0B66 (target_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_email_verification (id INT AUTO_INCREMENT NOT NULL, author_id INT NOT NULL, email VARCHAR(255) NOT NULL, token VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_A3A6C5A3F675F31B (author_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE content ADD CONSTRAINT FK_FEC530A9464E68B FOREIGN KEY (attachment_id) REFERENCES attachment (id)');
        $this->addSql('ALTER TABLE content ADD CONSTRAINT FK_FEC530A9A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE revise ADD CONSTRAINT FK_96624559F675F31B FOREIGN KEY (author_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE revise ADD CONSTRAINT FK_96624559158E0B66 FOREIGN KEY (target_id) REFERENCES content (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_email_verification ADD CONSTRAINT FK_A3A6C5A3F675F31B FOREIGN KEY (author_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE content DROP FOREIGN KEY FK_FEC530A9464E68B');
        $this->addSql('ALTER TABLE content DROP FOREIGN KEY FK_FEC530A9A76ED395');
        $this->addSql('ALTER TABLE revise DROP FOREIGN KEY FK_96624559F675F31B');
        $this->addSql('ALTER TABLE revise DROP FOREIGN KEY FK_96624559158E0B66');
        $this->addSql('ALTER TABLE user_email_verification DROP FOREIGN KEY FK_A3A6C5A3F675F31B');
        $this->addSql('DROP TABLE attachment');
        $this->addSql('DROP TABLE contact_request');
        $this->addSql('DROP TABLE content');
        $this->addSql('DROP TABLE revise');
        $this->addSql('DROP TABLE user_email_verification');
    }
}
