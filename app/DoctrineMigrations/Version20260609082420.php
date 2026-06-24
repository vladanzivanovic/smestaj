<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260609082420 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create ads_info_page, ads_info_page_image, ads_info_page_distance tables for property info pages';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE ads_info_page (id INT AUTO_INCREMENT NOT NULL, slug VARCHAR(255) NOT NULL, published TINYINT DEFAULT 0 NOT NULL, property_name VARCHAR(255) NOT NULL, tagline_rs LONGTEXT DEFAULT NULL, tagline_en LONGTEXT DEFAULT NULL, short_description_rs LONGTEXT DEFAULT NULL, short_description_en LONGTEXT DEFAULT NULL, welcome_message_rs LONGTEXT DEFAULT NULL, welcome_message_en LONGTEXT DEFAULT NULL, host_first_name VARCHAR(100) DEFAULT NULL, host_last_name VARCHAR(100) DEFAULT NULL, host_photo_filename VARCHAR(255) DEFAULT NULL, host_landline VARCHAR(50) DEFAULT NULL, host_mobile VARCHAR(50) DEFAULT NULL, instagram_url VARCHAR(255) DEFAULT NULL, facebook_url VARCHAR(255) DEFAULT NULL, whatsapp_url VARCHAR(255) DEFAULT NULL, viber_url VARCHAR(255) DEFAULT NULL, booking_url VARCHAR(255) DEFAULT NULL, airbnb_url VARCHAR(255) DEFAULT NULL, contact_email VARCHAR(255) DEFAULT NULL, google_review_input VARCHAR(500) DEFAULT NULL, address_street VARCHAR(255) DEFAULT NULL, address_postal_code VARCHAR(30) DEFAULT NULL, address_city VARCHAR(150) DEFAULT NULL, google_maps_lat DOUBLE PRECISION DEFAULT NULL, google_maps_lng DOUBLE PRECISION DEFAULT NULL, check_in_time TIME DEFAULT NULL, check_out_time TIME DEFAULT NULL, amenities LONGTEXT DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, deleted_at DATETIME DEFAULT NULL, linked_ads_id INT NOT NULL, UNIQUE INDEX UNIQ_73F28B19989D9B62 (slug), UNIQUE INDEX UNIQ_73F28B19DD3BDCD1 (linked_ads_id), INDEX IDX_ads_info_page_deleted_at (deleted_at), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE ads_info_page_distance (id INT AUTO_INCREMENT NOT NULL, label_rs VARCHAR(150) DEFAULT NULL, label_en VARCHAR(150) DEFAULT NULL, value_meters INT NOT NULL, position INT DEFAULT 0 NOT NULL, info_page_id INT NOT NULL, INDEX IDX_4237E6D88E834A7A (info_page_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE ads_info_page_image (id INT AUTO_INCREMENT NOT NULL, filename VARCHAR(500) DEFAULT NULL, original_name VARCHAR(500) DEFAULT NULL, position INT DEFAULT 0 NOT NULL, created_at DATETIME DEFAULT NULL, info_page_id INT NOT NULL, INDEX IDX_D73232368E834A7A (info_page_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE ads_info_page ADD CONSTRAINT FK_73F28B19DD3BDCD1 FOREIGN KEY (linked_ads_id) REFERENCES ads (Id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ads_info_page_distance ADD CONSTRAINT FK_4237E6D88E834A7A FOREIGN KEY (info_page_id) REFERENCES ads_info_page (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ads_info_page_image ADD CONSTRAINT FK_D73232368E834A7A FOREIGN KEY (info_page_id) REFERENCES ads_info_page (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ads_info_page DROP FOREIGN KEY FK_73F28B19DD3BDCD1');
        $this->addSql('ALTER TABLE ads_info_page_distance DROP FOREIGN KEY FK_4237E6D88E834A7A');
        $this->addSql('ALTER TABLE ads_info_page_image DROP FOREIGN KEY FK_D73232368E834A7A');
        $this->addSql('DROP TABLE ads_info_page_distance');
        $this->addSql('DROP TABLE ads_info_page_image');
        $this->addSql('DROP TABLE ads_info_page');
    }
}
