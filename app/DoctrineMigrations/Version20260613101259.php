<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260613101259 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Drop unused host_landline and contact_email columns from ads_info_page';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ads_info_page DROP host_landline, DROP contact_email');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ads_info_page ADD host_landline VARCHAR(50) DEFAULT NULL, ADD contact_email VARCHAR(255) DEFAULT NULL');
    }
}
