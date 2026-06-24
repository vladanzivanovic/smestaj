<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260610171757 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename ads_info_page.viber_url → viber_phone and whatsapp_url → whatsapp_phone (VARCHAR(50)); best-effort regex-extract digits from existing values.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE ads_info_page CHANGE viber_url viber_phone VARCHAR(50) DEFAULT NULL");
        $this->addSql("ALTER TABLE ads_info_page CHANGE whatsapp_url whatsapp_phone VARCHAR(50) DEFAULT NULL");
        $this->addSql("UPDATE ads_info_page SET viber_phone = NULLIF(REGEXP_REPLACE(viber_phone, '[^0-9+]', ''), '')");
        $this->addSql("UPDATE ads_info_page SET whatsapp_phone = NULLIF(REGEXP_REPLACE(whatsapp_phone, '[^0-9+]', ''), '')");

        $this->write('Best-effort digit extraction applied to viber_phone / whatsapp_phone; unparseable values became NULL.');
    }

    public function down(Schema $schema): void
    {
        $this->addSql("ALTER TABLE ads_info_page CHANGE viber_phone viber_url VARCHAR(255) DEFAULT NULL");
        $this->addSql("ALTER TABLE ads_info_page CHANGE whatsapp_phone whatsapp_url VARCHAR(255) DEFAULT NULL");

        $this->write('Down() restores column names/lengths; the original URL strings are NOT recoverable — restore from /var/www/html/backups/pre-info-page-phone-rename-*.sql if needed.');
    }
}
