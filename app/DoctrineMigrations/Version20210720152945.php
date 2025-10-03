<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210720152945 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("ALTER TABLE ads_payed_date DROP INDEX IDX_C38DD9007EC9F620, ADD UNIQUE INDEX UNIQ_C38DD9007EC9F620 (ads);");
        $this->addSql("ALTER TABLE ads_payed_date CHANGE ads ads INT DEFAULT NULL;");
        $this->addSql("ALTER TABLE ads_has_tags CHANGE ads ads INT DEFAULT NULL, CHANGE tag tag INT DEFAULT NULL;");
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
