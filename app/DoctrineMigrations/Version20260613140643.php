<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260613140643 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add wifi_username, wifi_password, house_rules columns to ads_info_page';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ads_info_page ADD wifi_username VARCHAR(100) DEFAULT NULL, ADD wifi_password VARCHAR(100) DEFAULT NULL, ADD house_rules LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ads_info_page DROP wifi_username, DROP wifi_password, DROP house_rules');
    }
}
