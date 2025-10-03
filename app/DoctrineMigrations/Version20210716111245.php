<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210716111245 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("ALTER TABLE users CHANGE CityId city INT DEFAULT NULL, change Status status SMALLINT DEFAULT 0 NOT NULL, CHANGE FirstName first_name VARCHAR(100) NOT NULL, CHANGE LastName last_name VARCHAR(100) NOT NULL, ADD discr VARCHAR(255) NOT NULL, CHANGE MobilePhone mobile_phone VARCHAR(150) DEFAULT NULL, CHANGE ContactEmail contact_email VARCHAR(250) DEFAULT NULL, CHANGE Address address VARCHAR(300) DEFAULT NULL, CHANGE Telephone telephone VARCHAR(100) DEFAULT NULL, CHANGE Password password VARCHAR(64) DEFAULT NULL, CHANGE Email email VARCHAR(60) DEFAULT NULL, CHANGE Viber viber VARCHAR(100) DEFAULT NULL, CHANGE token token VARCHAR(100) DEFAULT NULL, CHANGE token_valid token_valid DATETIME DEFAULT NULL, CHANGE Username username VARCHAR(100) DEFAULT NULL;
");
        $ads = $this->connection->fetchAllAssociative('SELECT * FROM ads');

        foreach ($ads as $ad) {
            $this->connection->update(
                'ads',
                ['owner_id' => $ad['SysCreatedUserId']],
                ['Id' => $ad['Id']]
            );
        }
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
