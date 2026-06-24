<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260612140328 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Refactor InfoPage amenities + distances onto ads_info_has_tag join; drop ads_info_page_distance + ads_info_page.amenities';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE ads_info_has_tag (id INT AUTO_INCREMENT NOT NULL, info_page_id INT NOT NULL, tag_id INT NOT NULL, value VARCHAR(100) NOT NULL, INDEX IDX_aiht_info_page (info_page_id), INDEX IDX_aiht_tag (tag_id), UNIQUE INDEX uniq_info_page_tag (info_page_id, tag_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE ads_info_has_tag ADD CONSTRAINT FK_aiht_info_page FOREIGN KEY (info_page_id) REFERENCES ads_info_page (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ads_info_has_tag ADD CONSTRAINT FK_aiht_tag FOREIGN KEY (tag_id) REFERENCES tag (id)');
        $this->addSql('DROP TABLE ads_info_page_distance');
        $this->addSql('ALTER TABLE ads_info_page DROP amenities');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ads_info_page ADD amenities LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\'');
        $this->addSql('CREATE TABLE ads_info_page_distance (id INT AUTO_INCREMENT NOT NULL, info_page_id INT NOT NULL, label_rs VARCHAR(150) DEFAULT NULL, label_en VARCHAR(150) DEFAULT NULL, value_meters INT NOT NULL, position INT DEFAULT 0 NOT NULL, INDEX IDX_4237E6D88E834A7A (info_page_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE ads_info_page_distance ADD CONSTRAINT FK_4237E6D88E834A7A FOREIGN KEY (info_page_id) REFERENCES ads_info_page (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ads_info_has_tag DROP FOREIGN KEY FK_aiht_info_page');
        $this->addSql('ALTER TABLE ads_info_has_tag DROP FOREIGN KEY FK_aiht_tag');
        $this->addSql('DROP TABLE ads_info_has_tag');
    }
}
