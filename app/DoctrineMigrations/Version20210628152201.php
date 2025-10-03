<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Result;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210628152201 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ads ADD owner_id INT DEFAULT NULL, ADD website VARCHAR(250) DEFAULT NULL, ADD facebook VARCHAR(250) DEFAULT NULL, CHANGE PrePriceTo PrePriceTo INT DEFAULT NULL, CHANGE PriceTo PriceTo INT DEFAULT NULL, CHANGE PostPriceTo PostPriceTo INT DEFAULT NULL, CHANGE lat lat DOUBLE PRECISION DEFAULT NULL, CHANGE lng lng DOUBLE PRECISION DEFAULT NULL, CHANGE address address VARCHAR(255) DEFAULT NULL, CHANGE SysCreatedUserId SysCreatedUserId INT DEFAULT NULL, CHANGE SysModifyUserId SysModifyUserId INT DEFAULT NULL, CHANGE CategoryId CategoryId INT DEFAULT NULL, CHANGE CityId CityId INT DEFAULT NULL');
        $this->addSql('ALTER TABLE ads ADD CONSTRAINT FK_7EC9F6207E3C61F9 FOREIGN KEY (owner_id) REFERENCES users (id)');
        $this->addSql('CREATE INDEX IDX_7EC9F6207E3C61F9 ON ads (owner_id)');

    }

    public function postUp(Schema $schema)
    {
        $users = $this->connection->fetchAllAssociative('SELECT * FROM users');

        foreach ($users as $user) {
            $this->connection->update(
                'ads',
                ['website' => $user['WebSite'], 'facebook' => $user['Facebook']],
                ['UserId' => $user['Id']]
            );
        }

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
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ads DROP FOREIGN KEY FK_7EC9F6207E3C61F9');
        $this->addSql('DROP INDEX IDX_7EC9F6207E3C61F9 ON ads');
        $this->addSql('ALTER TABLE ads DROP owner_id, DROP website, DROP facebook');
    }
}
