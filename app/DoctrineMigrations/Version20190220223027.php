<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190220223027 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql("INSERT INTO `tag_type` (`id`, `name`, `label`)
VALUES
	(1, 'Tip smeštaja', 'accomodation-type'),
	(2, 'Oprema', 'accomodation'),
	(3, 'Udaljenost', 'range'),
	(4, 'Usluge', 'services'),
	(5, 'Hrana', 'food');
");
        $this->addSql("INSERT INTO `tag` (`id`, `tag_type_id`, `name`, `icon`)
VALUES
	(1, 1, 'apartmani', ''),
	(3, 1, 'sobe sa kupatilom', ''),
	(4, 1, 'sobe sa zajedničkim kupatilom', ''),
	(7, 2, 'sopstveno kupatilo', 'soap-icon-tub'),
	(8, 2, 'WiFi Internet', 'soap-icon-wifi'),
	(9, 2, 'telefon', 'soap-icon-phone'),
	(10, 2, 'frižider', 'soap-icon-fridge'),
	(11, 2, 'parking', 'soap-icon-parking'),
	(12, 2, 'terasa', 'fa-pallet'),
	(13, 2, 'sef', 'fa-lock'),
	(14, 2, 'TV', 'soap-icon-television'),
	(15, 2, 'CATV', 'soap-icon-television'),
	(16, 2, 'bazen', 'soap-icon-swimming'),
	(17, 2, 'spa centar', 'fa-sun-o'),
	(18, 2, 'bar', 'soap-icon-winebar'),
	(19, 2, 'kuhinja', 'soap-icon-breakfast'),
	(20, 2, 'klima', 'soap-icon-aircon'),
	(21, 5, 'doručak', 'soap-icon-fork'),
	(24, 5, 'ručak', 'soap-icon-fork'),
	(25, 5, 'večera', 'soap-icon-fork'),
	(26, 5, 'all inclusive', 'soap-icon-fork'),
	(27, 3, 'plaže', 'soap-icon-beach'),
	(28, 3, 'prodavnice', 'soap-icon-shopping-1'),
	(29, 3, 'pošte', 'soap-icon-letter'),
	(30, 3, 'centra', 'soap-icon-error'),
	(31, 3, 'autobuske stanice', 'fa-bus'),
	(32, 3, 'železničke stanice', 'fa-train'),
	(33, 3, 'skijališta', 'soap-icon-ski'),
	(34, 3, 'jezera', 'soap-icon-swimming'),
	(35, 3, 'doma zdravlja', 'fa-hospital-o'),
	(36, 3, 'sportskih terena', 'soap-icon-playplace'),
	(37, 3, 'diskoteke', 'soap-icon-horn'),
	(38, 3, 'kafića', 'soap-icon-coffee'),
	(39, 3, 'restorana', 'soap-icon-breakfast'),
	(40, 3, 'bankomata', 'fa-suitcase'),
	(41, 3, 'reke', 'soap-icon-swimming'),
	(42, 3, 'ZOO vrta', 'soap-icon-dog'),
	(43, 3, 'aerodroma', 'soap-icon-plane-left'),
	(44, 3, 'aqua parka', 'soap-icon-swimming'),
	(45, 4, 'prevoz gostiju', 'soap-icon-pickanddrop'),
	(46, 4, 'izlet barkom', 'soap-icon-cruise-2'),
	(48, 4, 'vožnja gliserom', 'soap-icon-cruise'),
	(49, 4, 'turistički obilazak', 'soap-icon-cruise-3');
");
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
