<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190215231119 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql("INSERT INTO `role` (`id`, `Name`, `Code`) VALUES
	          (1, 'Admin', 'ROLE_ADMIN'),
	          (2, 'User', 'ROLE_USER'),
	          (3, 'Advanced User', 'ROLE_ADVANCED_USER'),
	          (4, 'Contact', 'ROLE_CONTACT');
	          ");

        $this->addSql("INSERT INTO `category` (`Id`, `Name`, `Alias`, `Image`, `SysCreatedTime`, `SysModifyTime`, `Status`, `SysCreatedUserId`, `SysModifyUserId`, `ParentId`)
VALUES
	(1, 'Sobe-Apartmani', 'sobe-apartmani', 'apartments.jpg', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, NULL, NULL, NULL),
	(2, 'Hoteli', 'hotel', 'hotels.jpg', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, NULL, NULL, NULL),
	(3, 'Hosteli', 'hostel', 'hostel.jpg', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, NULL, NULL, NULL),
	(4, 'Kuće', 'kuce', 'house.jpg', '0000-00-00 00:00:00', '0000-00-00 00:00:00', 1, NULL, NULL, NULL);
");
        $this->addSql("INSERT INTO `city` (`Id`, `Name`, `Alias`, `ZipCode`, `show_in_home`)
VALUES
	(134045, 'Bar', 'bar', NULL, 1),
	(134046, 'Čanj', 'canj', NULL, NULL),
	(134047, 'Dobra Voda', 'dobra-voda', NULL, 1),
	(134048, 'Ratac', 'ratac', NULL, NULL),
	(134049, 'Šušanj', 'susanj', NULL, NULL),
	(134050, 'Sutomore', 'sutomore', NULL, 1),
	(134051, 'Kolašin', 'kolasin', NULL, NULL),
	(134052, 'Baošići', 'baosici', NULL, NULL),
	(134053, 'Bijela', 'bjela', NULL, NULL),
	(134054, 'Dobra', 'dobra', NULL, NULL),
	(134055, 'Herceg Novi', 'herceg-novi', NULL, 1),
	(134056, 'Igalo', 'igalo', NULL, 0),
	(134057, 'Kotor', 'kotor', NULL, 1),
	(134058, 'Kumbor', 'kumbor', NULL, NULL),
	(134059, 'Ljuta', 'ljuta', NULL, NULL),
	(134060, 'Meljine', 'meljine', NULL, NULL),
	(134061, 'Njivice', 'njivice', NULL, NULL),
	(134062, 'Orahovac', 'orahovac', NULL, NULL),
	(134063, 'Perast', 'perast', NULL, NULL),
	(134064, 'Prčanj', 'prcanj', NULL, NULL),
	(134065, 'Radovići', 'radovici', NULL, NULL),
	(134066, 'Risan', 'risan', NULL, NULL),
	(134067, 'Stoliv', 'stoliv', NULL, 1),
	(134068, 'Tivat', 'tivat', NULL, NULL),
	(134069, 'Bečići', 'becici', NULL, 1),
	(134070, 'Budva', 'budva', NULL, 1),
	(134071, 'Miločer', 'milocer', NULL, NULL),
	(134072, 'Petrovac', 'petrovac', NULL, 1),
	(134073, 'Pržno', 'przno', NULL, NULL),
	(134074, 'Rafailovići', 'rafailovici', NULL, NULL),
	(134075, 'Sveti Stefan', 'sveti-stefan', NULL, 1),
	(134076, 'Žabljak', 'zabljak', NULL, NULL),
	(134077, 'Berane', 'berane', NULL, NULL),
	(134078, 'Cetinje', 'cetinje', NULL, NULL),
	(134079, 'Ada Bojana', 'ada-bojana', NULL, 1),
	(134080, 'Ulcinj', 'ulcinj', NULL, 1),
	(134081, 'Podgorica', 'podgorica', NULL, NULL),
	(134082, 'Nikšić', 'niksic', NULL, NULL),
	(134083, 'Buljarice', 'buljarice', NULL, NULL),
	(134084, 'Đenovići', 'djenovici', NULL, NULL),
	(134085, 'Krasići', 'krasici', NULL, NULL),
	(134086, 'Utjeha', 'utjeha', NULL, NULL),
	(134087, 'Kamenari', 'kamenari', NULL, NULL),
	(134088, 'Jaz', 'jaz', NULL, NULL),
	(134089, 'Komovi', 'komovi', NULL, NULL);
");
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
