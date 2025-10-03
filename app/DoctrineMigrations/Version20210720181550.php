<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210720181550 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("ALTER TABLE media DROP FOREIGN KEY FK_6A2CA10CE86B4685;");
        $this->addSql("DROP INDEX IDX_6A2CA10CE86B4685 ON media;");
        $this->addSql("ALTER TABLE media DROP SysCreatedTime, DROP SysCreatorId, CHANGE AdsId AdsId INT DEFAULT NULL, CHANGE AdsInfoId AdsInfoId INT DEFAULT NULL;");
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
