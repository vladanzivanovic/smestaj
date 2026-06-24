<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260613181730 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Purge soft-deleted AdsInfoPage rows and drop deleted_at column + index';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DELETE FROM ads_info_page WHERE deleted_at IS NOT NULL');
        $this->addSql('DROP INDEX IDX_ads_info_page_deleted_at ON ads_info_page');
        $this->addSql('ALTER TABLE ads_info_page DROP COLUMN deleted_at');
    }

    // NOTE: purged rows are NOT restored — irreversibility accepted by user (CONTEXT_SPEC.md §D).
    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ads_info_page ADD COLUMN deleted_at DATETIME DEFAULT NULL');
        $this->addSql('CREATE INDEX IDX_ads_info_page_deleted_at ON ads_info_page (deleted_at)');
    }
}
