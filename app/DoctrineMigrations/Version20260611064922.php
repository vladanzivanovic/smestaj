<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260611064922 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add ads_info_page_image.is_main boolean (per-page main flag) + backfill first image of each page as main';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ads_info_page_image ADD is_main TINYINT(1) DEFAULT 0 NOT NULL');

        // Backfill: mark the first image of each AdsInfoPage as main.
        // Ordering matches AdsInfoPage::$images OrderBy(['position' => 'ASC']);
        // tiebreaker is id ASC (smallest PK wins when multiple rows share position).
        $this->addSql(
            'UPDATE ads_info_page_image AS aipi
             INNER JOIN (
                 SELECT t.info_page_id, MIN(t.id) AS first_id
                 FROM ads_info_page_image AS t
                 INNER JOIN (
                     SELECT info_page_id, MIN(position) AS min_pos
                     FROM ads_info_page_image
                     GROUP BY info_page_id
                 ) AS mp ON mp.info_page_id = t.info_page_id AND mp.min_pos = t.position
                 GROUP BY t.info_page_id
             ) AS firsts ON firsts.first_id = aipi.id
             SET aipi.is_main = 1'
        );

        $this->write('Backfilled is_main on the first (position ASC, id ASC) image of each AdsInfoPage.');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ads_info_page_image DROP is_main');
    }
}
