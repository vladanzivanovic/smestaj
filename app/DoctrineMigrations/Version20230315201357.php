<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230315201357 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE badgetoad DROP FOREIGN KEY FK_22E8F6F1626B2120');
        $this->addSql('DROP TABLE badges');
        $this->addSql('DROP TABLE badgetoad');
        $this->addSql('ALTER TABLE ads_payed_date DROP INDEX UNIQ_C38DD9007EC9F620, ADD INDEX IDX_C38DD9007EC9F620 (ads)');
        $this->addSql('ALTER TABLE ads_payed_date ADD type SMALLINT NOT NULL, ADD status SMALLINT DEFAULT 0 NOT NULL, CHANGE ads ads INT DEFAULT NULL');
        $this->addSql('set foreign_key_checks = 0;ALTER TABLE ads_payed_date ADD CONSTRAINT FK_C38DD9007EC9F620 FOREIGN KEY (ads) REFERENCES ads (Id);set foreign_key_checks = 1;');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
