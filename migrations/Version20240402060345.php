<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240402060345 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE amenity (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(128) NOT NULL, slug VARCHAR(128) NOT NULL, icon VARCHAR(50) DEFAULT NULL, is_online TINYINT(1) DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_AB607963989D9B62 (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE audience (id INT AUTO_INCREMENT NOT NULL, image_name VARCHAR(50) DEFAULT NULL, image_size INT UNSIGNED DEFAULT NULL, image_mime_type VARCHAR(50) DEFAULT NULL, image_original_name VARCHAR(1000) DEFAULT NULL, image_dimensions LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', name VARCHAR(128) NOT NULL, slug VARCHAR(128) NOT NULL, is_online TINYINT(1) DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_FDCD9418989D9B62 (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE cart_element (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, recipesubscription_id INT DEFAULT NULL, quantity INT DEFAULT NULL, subscription_fee NUMERIC(10, 2) DEFAULT NULL, reserved_seats JSON DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_BA9A963EA76ED395 (user_id), INDEX IDX_BA9A963E34F648C0 (recipesubscription_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `order` (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, payment_gateway_id INT DEFAULT NULL, payment_id INT DEFAULT NULL, note LONGTEXT DEFAULT NULL, subscription_fee NUMERIC(10, 2) NOT NULL, subscription_price_percentage_cut INT NOT NULL, status INT NOT NULL, currency_ccy VARCHAR(10) NOT NULL, currency_symbol VARCHAR(10) NOT NULL, reference VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_F5299398AEA34913 (reference), INDEX IDX_F5299398A76ED395 (user_id), INDEX IDX_F529939862890FD5 (payment_gateway_id), UNIQUE INDEX UNIQ_F52993984C3A3BB (payment_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE order_element (id INT AUTO_INCREMENT NOT NULL, recipesubscription_id INT DEFAULT NULL, order_id INT DEFAULT NULL, unitprice NUMERIC(10, 2) DEFAULT NULL, quantity INT DEFAULT NULL, reserved_seats JSON DEFAULT NULL, deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_B73AF77234F648C0 (recipesubscription_id), INDEX IDX_B73AF7728D9F6D38 (order_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE order_subscription (id INT AUTO_INCREMENT NOT NULL, orderelement_id INT DEFAULT NULL, is_scanned TINYINT(1) DEFAULT 0 NOT NULL, reference VARCHAR(20) NOT NULL, reserved_seat JSON DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_A706F0B9EE04F0C1 (orderelement_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE payment (id INT AUTO_INCREMENT NOT NULL, country_id INT DEFAULT NULL, order_id INT DEFAULT NULL, firstname VARCHAR(20) DEFAULT NULL, lastname VARCHAR(20) DEFAULT NULL, street VARCHAR(50) DEFAULT NULL, street2 VARCHAR(50) DEFAULT NULL, city VARCHAR(50) DEFAULT NULL, state VARCHAR(50) DEFAULT NULL, postalcode VARCHAR(15) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_6D28840DF92F3E70 (country_id), UNIQUE INDEX UNIQ_6D28840D8D9F6D38 (order_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE payment_gateway (id INT AUTO_INCREMENT NOT NULL, restaurant_id INT DEFAULT NULL, gateway_logo_name VARCHAR(50) DEFAULT NULL, instructions LONGTEXT DEFAULT NULL, number INT DEFAULT NULL, name VARCHAR(128) NOT NULL, slug VARCHAR(128) NOT NULL, is_online TINYINT(1) DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_DB7D395989D9B62 (slug), INDEX IDX_DB7D395B1E7706E (restaurant_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE payment_token (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE payout_request (id INT AUTO_INCREMENT NOT NULL, restaurant_id INT DEFAULT NULL, recipe_date_id INT DEFAULT NULL, payment_gateway_id INT DEFAULT NULL, payment JSON DEFAULT NULL, note LONGTEXT DEFAULT NULL, status INT NOT NULL, reference VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_5AC7D4CAEA34913 (reference), INDEX IDX_5AC7D4CB1E7706E (restaurant_id), INDEX IDX_5AC7D4CF473382B (recipe_date_id), INDEX IDX_5AC7D4C62890FD5 (payment_gateway_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE recipe_audience (recipe_id INT NOT NULL, audience_id INT NOT NULL, INDEX IDX_8B5D318659D8A214 (recipe_id), INDEX IDX_8B5D3186848CC616 (audience_id), PRIMARY KEY(recipe_id, audience_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE recipe_date (id INT AUTO_INCREMENT NOT NULL, recipe_id INT DEFAULT NULL, venue_id INT DEFAULT NULL, seating_plan_id INT DEFAULT NULL, has_seating_plan TINYINT(1) DEFAULT NULL, startdate DATETIME DEFAULT NULL, enddate DATETIME DEFAULT NULL, is_active TINYINT(1) DEFAULT 0 NOT NULL, is_online TINYINT(1) DEFAULT 0 NOT NULL, reference VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_D5856DA5AEA34913 (reference), INDEX IDX_D5856DA559D8A214 (recipe_id), INDEX IDX_D5856DA540A73EBA (venue_id), INDEX IDX_D5856DA519E3A7BA (seating_plan_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE recipedate_pointofsale (recipedate_id INT NOT NULL, pointofsale_id INT NOT NULL, INDEX IDX_98C66A8BCCBACFC9 (recipedate_id), INDEX IDX_98C66A8B18E07BF3 (pointofsale_id), PRIMARY KEY(recipedate_id, pointofsale_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE recipedate_scanner (recipedate_id INT NOT NULL, scanner_id INT NOT NULL, INDEX IDX_E647244ECCBACFC9 (recipedate_id), INDEX IDX_E647244E67C89E33 (scanner_id), PRIMARY KEY(recipedate_id, scanner_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE recipe_subscription (id INT AUTO_INCREMENT NOT NULL, recipedate_id INT DEFAULT NULL, name VARCHAR(50) NOT NULL, description LONGTEXT DEFAULT NULL, is_free TINYINT(1) DEFAULT 0 NOT NULL, price NUMERIC(10, 2) DEFAULT NULL, promotional_price NUMERIC(10, 2) DEFAULT NULL, quantity INT DEFAULT NULL, subscriptionsperattendee INT DEFAULT NULL, salesstartdate DATETIME DEFAULT NULL, salesenddate DATETIME DEFAULT NULL, position INT NOT NULL, reserved_seat JSON DEFAULT NULL, seating_plan_sections LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', is_active TINYINT(1) DEFAULT 0 NOT NULL, reference VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_E9CD33DCAEA34913 (reference), INDEX IDX_E9CD33DCCCBACFC9 (recipedate_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE subscription_reservation (id INT AUTO_INCREMENT NOT NULL, orderelement_id INT DEFAULT NULL, user_id INT DEFAULT NULL, recipesubscription_id INT DEFAULT NULL, quantity INT DEFAULT NULL, expires_at DATETIME DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_7FD239C2EE04F0C1 (orderelement_id), INDEX IDX_7FD239C2A76ED395 (user_id), INDEX IDX_7FD239C234F648C0 (recipesubscription_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE venue_amenity (venue_id INT NOT NULL, amenity_id INT NOT NULL, INDEX IDX_1BA88EBD40A73EBA (venue_id), INDEX IDX_1BA88EBD9F9F1305 (amenity_id), PRIMARY KEY(venue_id, amenity_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE venue_image (id INT AUTO_INCREMENT NOT NULL, venue_id INT DEFAULT NULL, image_name VARCHAR(50) DEFAULT NULL, position INT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_1D86098840A73EBA (venue_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE venue_seating_plan (id INT AUTO_INCREMENT NOT NULL, venue_id INT DEFAULT NULL, design JSON DEFAULT NULL, name VARCHAR(128) NOT NULL, slug VARCHAR(128) NOT NULL, UNIQUE INDEX UNIQ_8B64BB51989D9B62 (slug), INDEX IDX_8B64BB5140A73EBA (venue_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE venue_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(128) NOT NULL, slug VARCHAR(128) NOT NULL, is_online TINYINT(1) DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_3AF85A81989D9B62 (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE cart_element ADD CONSTRAINT FK_BA9A963EA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE cart_element ADD CONSTRAINT FK_BA9A963E34F648C0 FOREIGN KEY (recipesubscription_id) REFERENCES recipe_subscription (id)');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F529939862890FD5 FOREIGN KEY (payment_gateway_id) REFERENCES payment_gateway (id)');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F52993984C3A3BB FOREIGN KEY (payment_id) REFERENCES payment (id)');
        $this->addSql('ALTER TABLE order_element ADD CONSTRAINT FK_B73AF77234F648C0 FOREIGN KEY (recipesubscription_id) REFERENCES recipe_subscription (id)');
        $this->addSql('ALTER TABLE order_element ADD CONSTRAINT FK_B73AF7728D9F6D38 FOREIGN KEY (order_id) REFERENCES `order` (id)');
        $this->addSql('ALTER TABLE order_subscription ADD CONSTRAINT FK_A706F0B9EE04F0C1 FOREIGN KEY (orderelement_id) REFERENCES order_element (id)');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840DF92F3E70 FOREIGN KEY (country_id) REFERENCES country (id)');
        $this->addSql('ALTER TABLE payment ADD CONSTRAINT FK_6D28840D8D9F6D38 FOREIGN KEY (order_id) REFERENCES `order` (id)');
        $this->addSql('ALTER TABLE payment_gateway ADD CONSTRAINT FK_DB7D395B1E7706E FOREIGN KEY (restaurant_id) REFERENCES restaurant (id)');
        $this->addSql('ALTER TABLE payout_request ADD CONSTRAINT FK_5AC7D4CB1E7706E FOREIGN KEY (restaurant_id) REFERENCES restaurant (id)');
        $this->addSql('ALTER TABLE payout_request ADD CONSTRAINT FK_5AC7D4CF473382B FOREIGN KEY (recipe_date_id) REFERENCES recipe_date (id)');
        $this->addSql('ALTER TABLE payout_request ADD CONSTRAINT FK_5AC7D4C62890FD5 FOREIGN KEY (payment_gateway_id) REFERENCES payment_gateway (id)');
        $this->addSql('ALTER TABLE recipe_audience ADD CONSTRAINT FK_8B5D318659D8A214 FOREIGN KEY (recipe_id) REFERENCES recipe (id)');
        $this->addSql('ALTER TABLE recipe_audience ADD CONSTRAINT FK_8B5D3186848CC616 FOREIGN KEY (audience_id) REFERENCES audience (id)');
        $this->addSql('ALTER TABLE recipe_date ADD CONSTRAINT FK_D5856DA559D8A214 FOREIGN KEY (recipe_id) REFERENCES recipe (id)');
        $this->addSql('ALTER TABLE recipe_date ADD CONSTRAINT FK_D5856DA540A73EBA FOREIGN KEY (venue_id) REFERENCES venue (id)');
        $this->addSql('ALTER TABLE recipe_date ADD CONSTRAINT FK_D5856DA519E3A7BA FOREIGN KEY (seating_plan_id) REFERENCES venue_seating_plan (id)');
        $this->addSql('ALTER TABLE recipedate_pointofsale ADD CONSTRAINT FK_98C66A8BCCBACFC9 FOREIGN KEY (recipedate_id) REFERENCES recipe_date (id)');
        $this->addSql('ALTER TABLE recipedate_pointofsale ADD CONSTRAINT FK_98C66A8B18E07BF3 FOREIGN KEY (pointofsale_id) REFERENCES scanner (id)');
        $this->addSql('ALTER TABLE recipedate_scanner ADD CONSTRAINT FK_E647244ECCBACFC9 FOREIGN KEY (recipedate_id) REFERENCES recipe_date (id)');
        $this->addSql('ALTER TABLE recipedate_scanner ADD CONSTRAINT FK_E647244E67C89E33 FOREIGN KEY (scanner_id) REFERENCES point_of_sale (id)');
        $this->addSql('ALTER TABLE recipe_subscription ADD CONSTRAINT FK_E9CD33DCCCBACFC9 FOREIGN KEY (recipedate_id) REFERENCES recipe_date (id)');
        $this->addSql('ALTER TABLE subscription_reservation ADD CONSTRAINT FK_7FD239C2EE04F0C1 FOREIGN KEY (orderelement_id) REFERENCES order_element (id)');
        $this->addSql('ALTER TABLE subscription_reservation ADD CONSTRAINT FK_7FD239C2A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE subscription_reservation ADD CONSTRAINT FK_7FD239C234F648C0 FOREIGN KEY (recipesubscription_id) REFERENCES recipe_subscription (id)');
        $this->addSql('ALTER TABLE venue_amenity ADD CONSTRAINT FK_1BA88EBD40A73EBA FOREIGN KEY (venue_id) REFERENCES venue (id)');
        $this->addSql('ALTER TABLE venue_amenity ADD CONSTRAINT FK_1BA88EBD9F9F1305 FOREIGN KEY (amenity_id) REFERENCES amenity (id)');
        $this->addSql('ALTER TABLE venue_image ADD CONSTRAINT FK_1D86098840A73EBA FOREIGN KEY (venue_id) REFERENCES venue (id)');
        $this->addSql('ALTER TABLE venue_seating_plan ADD CONSTRAINT FK_8B64BB5140A73EBA FOREIGN KEY (venue_id) REFERENCES venue (id)');
        $this->addSql('ALTER TABLE point_of_sale CHANGE restaurant_id restaurant_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE post ADD image_name VARCHAR(50) DEFAULT NULL, DROP thumbnail');
        $this->addSql('ALTER TABLE pricing ADD image_name VARCHAR(50) DEFAULT NULL, DROP thumbnail');
        $this->addSql('ALTER TABLE scanner CHANGE restaurant_id restaurant_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649FF1B2680');
        $this->addSql('DROP INDEX IDX_8D93D649FF1B2680 ON user');
        $this->addSql('ALTER TABLE user ADD country_id INT DEFAULT NULL, ADD street VARCHAR(50) DEFAULT NULL, ADD street2 VARCHAR(50) DEFAULT NULL, ADD postalcode VARCHAR(15) DEFAULT NULL, ADD city VARCHAR(50) DEFAULT NULL, ADD state VARCHAR(50) DEFAULT NULL, ADD phone VARCHAR(50) DEFAULT NULL, ADD birthdate DATE DEFAULT NULL, DROP country, DROP externallink, DROP youtubeurl, DROP twitterurl, DROP instagramurl, DROP facebookurl, DROP googleplusurl, DROP linkedinurl, DROP avatar, CHANGE isuseronhomepageslider_id isrestaurantonhomepageslider_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649F075C49F FOREIGN KEY (isrestaurantonhomepageslider_id) REFERENCES homepage_hero_setting (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649F92F3E70 FOREIGN KEY (country_id) REFERENCES country (id)');
        $this->addSql('CREATE INDEX IDX_8D93D649F075C49F ON user (isrestaurantonhomepageslider_id)');
        $this->addSql('CREATE INDEX IDX_8D93D649F92F3E70 ON user (country_id)');
        $this->addSql('ALTER TABLE venue ADD type_id INT DEFAULT NULL, CHANGE restaurant_id restaurant_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE venue ADD CONSTRAINT FK_91911B0DC54C8C93 FOREIGN KEY (type_id) REFERENCES venue_type (id)');
        $this->addSql('CREATE INDEX IDX_91911B0DC54C8C93 ON venue (type_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE venue DROP FOREIGN KEY FK_91911B0DC54C8C93');
        $this->addSql('ALTER TABLE cart_element DROP FOREIGN KEY FK_BA9A963EA76ED395');
        $this->addSql('ALTER TABLE cart_element DROP FOREIGN KEY FK_BA9A963E34F648C0');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398A76ED395');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F529939862890FD5');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F52993984C3A3BB');
        $this->addSql('ALTER TABLE order_element DROP FOREIGN KEY FK_B73AF77234F648C0');
        $this->addSql('ALTER TABLE order_element DROP FOREIGN KEY FK_B73AF7728D9F6D38');
        $this->addSql('ALTER TABLE order_subscription DROP FOREIGN KEY FK_A706F0B9EE04F0C1');
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840DF92F3E70');
        $this->addSql('ALTER TABLE payment DROP FOREIGN KEY FK_6D28840D8D9F6D38');
        $this->addSql('ALTER TABLE payment_gateway DROP FOREIGN KEY FK_DB7D395B1E7706E');
        $this->addSql('ALTER TABLE payout_request DROP FOREIGN KEY FK_5AC7D4CB1E7706E');
        $this->addSql('ALTER TABLE payout_request DROP FOREIGN KEY FK_5AC7D4CF473382B');
        $this->addSql('ALTER TABLE payout_request DROP FOREIGN KEY FK_5AC7D4C62890FD5');
        $this->addSql('ALTER TABLE recipe_audience DROP FOREIGN KEY FK_8B5D318659D8A214');
        $this->addSql('ALTER TABLE recipe_audience DROP FOREIGN KEY FK_8B5D3186848CC616');
        $this->addSql('ALTER TABLE recipe_date DROP FOREIGN KEY FK_D5856DA559D8A214');
        $this->addSql('ALTER TABLE recipe_date DROP FOREIGN KEY FK_D5856DA540A73EBA');
        $this->addSql('ALTER TABLE recipe_date DROP FOREIGN KEY FK_D5856DA519E3A7BA');
        $this->addSql('ALTER TABLE recipedate_pointofsale DROP FOREIGN KEY FK_98C66A8BCCBACFC9');
        $this->addSql('ALTER TABLE recipedate_pointofsale DROP FOREIGN KEY FK_98C66A8B18E07BF3');
        $this->addSql('ALTER TABLE recipedate_scanner DROP FOREIGN KEY FK_E647244ECCBACFC9');
        $this->addSql('ALTER TABLE recipedate_scanner DROP FOREIGN KEY FK_E647244E67C89E33');
        $this->addSql('ALTER TABLE recipe_subscription DROP FOREIGN KEY FK_E9CD33DCCCBACFC9');
        $this->addSql('ALTER TABLE subscription_reservation DROP FOREIGN KEY FK_7FD239C2EE04F0C1');
        $this->addSql('ALTER TABLE subscription_reservation DROP FOREIGN KEY FK_7FD239C2A76ED395');
        $this->addSql('ALTER TABLE subscription_reservation DROP FOREIGN KEY FK_7FD239C234F648C0');
        $this->addSql('ALTER TABLE venue_amenity DROP FOREIGN KEY FK_1BA88EBD40A73EBA');
        $this->addSql('ALTER TABLE venue_amenity DROP FOREIGN KEY FK_1BA88EBD9F9F1305');
        $this->addSql('ALTER TABLE venue_image DROP FOREIGN KEY FK_1D86098840A73EBA');
        $this->addSql('ALTER TABLE venue_seating_plan DROP FOREIGN KEY FK_8B64BB5140A73EBA');
        $this->addSql('DROP TABLE amenity');
        $this->addSql('DROP TABLE audience');
        $this->addSql('DROP TABLE cart_element');
        $this->addSql('DROP TABLE `order`');
        $this->addSql('DROP TABLE order_element');
        $this->addSql('DROP TABLE order_subscription');
        $this->addSql('DROP TABLE payment');
        $this->addSql('DROP TABLE payment_gateway');
        $this->addSql('DROP TABLE payment_token');
        $this->addSql('DROP TABLE payout_request');
        $this->addSql('DROP TABLE recipe_audience');
        $this->addSql('DROP TABLE recipe_date');
        $this->addSql('DROP TABLE recipedate_pointofsale');
        $this->addSql('DROP TABLE recipedate_scanner');
        $this->addSql('DROP TABLE recipe_subscription');
        $this->addSql('DROP TABLE subscription_reservation');
        $this->addSql('DROP TABLE venue_amenity');
        $this->addSql('DROP TABLE venue_image');
        $this->addSql('DROP TABLE venue_seating_plan');
        $this->addSql('DROP TABLE venue_type');
        $this->addSql('ALTER TABLE point_of_sale CHANGE restaurant_id restaurant_id INT NOT NULL');
        $this->addSql('ALTER TABLE post ADD thumbnail VARCHAR(255) DEFAULT NULL, DROP image_name');
        $this->addSql('ALTER TABLE pricing ADD thumbnail VARCHAR(255) DEFAULT NULL, DROP image_name');
        $this->addSql('ALTER TABLE scanner CHANGE restaurant_id restaurant_id INT NOT NULL');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649F075C49F');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649F92F3E70');
        $this->addSql('DROP INDEX IDX_8D93D649F075C49F ON user');
        $this->addSql('DROP INDEX IDX_8D93D649F92F3E70 ON user');
        $this->addSql('ALTER TABLE user ADD isuseronhomepageslider_id INT DEFAULT NULL, ADD country VARCHAR(2) DEFAULT \'FR\', ADD externallink VARCHAR(255) DEFAULT \'http://example.com\', ADD youtubeurl VARCHAR(255) DEFAULT \'https://www.youtube.com\', ADD twitterurl VARCHAR(255) DEFAULT \'https://twitter.com/France/\', ADD instagramurl VARCHAR(255) DEFAULT \'https://www.instagram.com/\', ADD facebookurl VARCHAR(255) DEFAULT \'https://fr-fr.facebook.com/\', ADD googleplusurl VARCHAR(255) DEFAULT \'#\', ADD linkedinurl VARCHAR(255) DEFAULT \'https://fr.linkedin.com/\', ADD avatar VARCHAR(255) NOT NULL, DROP isrestaurantonhomepageslider_id, DROP country_id, DROP street, DROP street2, DROP postalcode, DROP city, DROP state, DROP phone, DROP birthdate');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649FF1B2680 FOREIGN KEY (isuseronhomepageslider_id) REFERENCES homepage_hero_setting (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_8D93D649FF1B2680 ON user (isuseronhomepageslider_id)');
        $this->addSql('DROP INDEX IDX_91911B0DC54C8C93 ON venue');
        $this->addSql('ALTER TABLE venue DROP type_id, CHANGE restaurant_id restaurant_id INT NOT NULL');
    }
}
