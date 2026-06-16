<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260612143757 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove host_photo_filename column from ads_info_page';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ads_info_page DROP host_photo_filename');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ads_info_page ADD host_photo_filename VARCHAR(255) DEFAULT NULL');
    }
}
