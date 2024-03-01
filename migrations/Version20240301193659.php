<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240301193659 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD country VARCHAR(2) DEFAULT \'FR\', ADD firstname VARCHAR(20) NOT NULL, ADD lastname VARCHAR(20) NOT NULL, ADD externallink VARCHAR(255) DEFAULT \'http://example.com\', ADD youtubeurl VARCHAR(255) DEFAULT \'https://www.youtube.com\', ADD twitterurl VARCHAR(255) DEFAULT \'https://twitter.com/France/\', ADD instagramurl VARCHAR(255) DEFAULT \'https://www.instagram.com/\', ADD facebookurl VARCHAR(255) DEFAULT \'https://fr-fr.facebook.com/\', ADD googleplusurl VARCHAR(255) DEFAULT \'#\', ADD linkedinurl VARCHAR(255) DEFAULT \'https://fr.linkedin.com/\', ADD deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP country, DROP firstname, DROP lastname, DROP externallink, DROP youtubeurl, DROP twitterurl, DROP instagramurl, DROP facebookurl, DROP googleplusurl, DROP linkedinurl, DROP deleted_at');
    }
}
