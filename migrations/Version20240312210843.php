<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240312210843 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE pricing (id INT AUTO_INCREMENT NOT NULL, thumbnail VARCHAR(255) DEFAULT NULL, title VARCHAR(255) NOT NULL, name VARCHAR(50) NOT NULL, color VARCHAR(50) NOT NULL, price DOUBLE PRECISION NOT NULL, duration INT NOT NULL, symbol VARCHAR(50) DEFAULT NULL, stripe_id VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE subscription (id INT AUTO_INCREMENT NOT NULL, pricing_id INT NOT NULL, user_id INT NOT NULL, state SMALLINT NOT NULL, next_payment DATETIME NOT NULL, created_at DATETIME NOT NULL, stripe_id VARCHAR(255) DEFAULT NULL, INDEX IDX_A3C664D38864AF73 (pricing_id), INDEX IDX_A3C664D3A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE transaction (id INT AUTO_INCREMENT NOT NULL, author_id INT NOT NULL, firstname VARCHAR(255) DEFAULT NULL, lastname VARCHAR(255) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, postal_code VARCHAR(255) DEFAULT NULL, country_code VARCHAR(255) DEFAULT NULL, fee DOUBLE PRECISION DEFAULT \'0\' NOT NULL, duration INT NOT NULL, price DOUBLE PRECISION NOT NULL, tax DOUBLE PRECISION NOT NULL, method VARCHAR(255) NOT NULL, method_ref VARCHAR(255) DEFAULT NULL, refunded TINYINT(1) DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_723705D1F675F31B (author_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE subscription ADD CONSTRAINT FK_A3C664D38864AF73 FOREIGN KEY (pricing_id) REFERENCES pricing (id)');
        $this->addSql('ALTER TABLE subscription ADD CONSTRAINT FK_A3C664D3A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D1F675F31B FOREIGN KEY (author_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE recipe ADD level SMALLINT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE user ADD invoice_info VARCHAR(255) DEFAULT NULL, ADD premium_end DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD stripe_id VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE subscription DROP FOREIGN KEY FK_A3C664D38864AF73');
        $this->addSql('ALTER TABLE subscription DROP FOREIGN KEY FK_A3C664D3A76ED395');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D1F675F31B');
        $this->addSql('DROP TABLE pricing');
        $this->addSql('DROP TABLE subscription');
        $this->addSql('DROP TABLE transaction');
        $this->addSql('ALTER TABLE recipe DROP level');
        $this->addSql('ALTER TABLE user DROP invoice_info, DROP premium_end, DROP stripe_id');
    }
}
