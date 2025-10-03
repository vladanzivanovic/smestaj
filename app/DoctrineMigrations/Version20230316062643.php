<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230316062643 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->connection->update(
            'ads_payed_date',
            [
                'type' => 10,
                'status' => 2
            ],
            [
                'type' => 0
            ]
        );
        // this up() migration is auto-generated, please modify it to your needs
        $payedAds = $this->connection->executeQuery('
            SELECT a.* FROM ads as a
            inner join ads_payed_date as apd on a.id = apd.ads
            where apd.id IS NOT NULL and
                  a.status = 2
        ');

        foreach ($payedAds->fetchAllAssociative() as $item) {
            $this->connection->insert(
                'ads_payed_date',
                [
                    'ads' => $item['Id'],
                    'type' => 10,
                    'status' => 2,
                    'date' => '2023-09-01 00:00:00',
                ]
            );
        }

        $ads = $this->connection->executeQuery('
            SELECT a.* FROM ads as a
            left join ads_payed_date as apd on a.id = apd.ads
            where apd.id IS NULL and
                  a.status = 2
        ');

        foreach ($ads->fetchAllAssociative() as $item) {
            $this->connection->insert(
                'ads_payed_date',
                [
                    'ads' => $item['Id'],
                    'type' => 1,
                    'status' => 2,
                    'date' => '2023-09-01 00:00:00',
                ]
            );
        }
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
