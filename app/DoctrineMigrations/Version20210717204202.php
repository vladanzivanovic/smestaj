<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210717204202 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("ALTER TABLE ads DROP FOREIGN KEY FK_7EC9F620631A48FA;");
        $this->addSql("DROP INDEX IDX_7EC9F620631A48FA ON ads;");
        $this->addSql("ALTER TABLE ads CHANGE owner_id owner_id INT DEFAULT NULL, CHANGE PrePriceTo PrePriceTo INT DEFAULT NULL, CHANGE PriceTo PriceTo INT DEFAULT NULL, CHANGE PostPriceTo PostPriceTo INT DEFAULT NULL, CHANGE lat lat DOUBLE PRECISION DEFAULT NULL, CHANGE lng lng DOUBLE PRECISION DEFAULT NULL, CHANGE address address VARCHAR(255) DEFAULT NULL, CHANGE website website VARCHAR(250) DEFAULT NULL, CHANGE facebook facebook VARCHAR(250) DEFAULT NULL, CHANGE instagram instagram VARCHAR(250) DEFAULT NULL, CHANGE SysCreatedUserId SysCreatedUserId INT DEFAULT NULL, CHANGE SysModifyUserId SysModifyUserId INT DEFAULT NULL, CHANGE CategoryId CategoryId INT DEFAULT NULL, CHANGE CityId CityId INT DEFAULT NULL, CHANGE userid contact INT NOT NULL;");
        $this->addSql("ALTER TABLE ads ADD CONSTRAINT FK_7EC9F6204C62E638 FOREIGN KEY (contact) REFERENCES users (Id);");
        $this->addSql("CREATE UNIQUE INDEX UNIQ_7EC9F6204C62E638 ON ads (contact);");
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
