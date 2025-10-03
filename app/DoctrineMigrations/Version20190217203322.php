<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190217203322 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql("INSERT INTO `category` (`Name`, `Alias`, `Image`, `SysCreatedTime`, `SysModifyTime`, `Status`, `SysCreatedUserId`, `SysModifyUserId`, `ParentId`)
VALUES
	('Stanovi', 'stanovi', 'flat.jpg', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, NULL, NULL, NULL),
	('Vile', 'vile', 'villas.jpg', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, NULL, NULL, NULL)
");

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
