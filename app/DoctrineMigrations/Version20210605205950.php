<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210605205950 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("
        ALTER TABLE userreservation CHANGE Email Email VARCHAR(250) DEFAULT NULL, CHANGE Telephone Telephone VARCHAR(250) DEFAULT NULL, CHANGE MobilePhone MobilePhone VARCHAR(200) DEFAULT NULL, CHANGE UserId UserId INT DEFAULT NULL, CHANGE CityId CityId INT DEFAULT NULL;
     ALTER TABLE category CHANGE Image Image VARCHAR(250) DEFAULT NULL, CHANGE Status Status SMALLINT NOT NULL, CHANGE SysCreatedUserId SysCreatedUserId INT DEFAULT NULL, CHANGE SysModifyUserId SysModifyUserId INT DEFAULT NULL, CHANGE ParentId ParentId INT DEFAULT NULL;
     ALTER TABLE media CHANGE SysCreatedTime SysCreatedTime DATETIME DEFAULT NULL, CHANGE SysCreatorId SysCreatorId INT DEFAULT NULL, CHANGE AdsId AdsId INT DEFAULT NULL, CHANGE AdsInfoId AdsInfoId INT DEFAULT NULL;
     ALTER TABLE reservation CHANGE AdultNumber AdultNumber INT DEFAULT NULL, CHANGE ChildrenNumber ChildrenNumber INT DEFAULT NULL, CHANGE Email Email VARCHAR(255) DEFAULT NULL, CHANGE Viber Viber VARCHAR(100) DEFAULT NULL;
     ALTER TABLE ads_has_tags CHANGE ads ads INT DEFAULT NULL, CHANGE tag tag INT DEFAULT NULL;
     ALTER TABLE youtubeinfo CHANGE SysCreatedTime SysCreatedTime DATETIME DEFAULT NULL, CHANGE SysCreatorId SysCreatorId INT DEFAULT NULL, CHANGE AdsId AdsId INT DEFAULT NULL;
     ALTER TABLE users CHANGE Username Username VARCHAR(100) DEFAULT NULL, CHANGE Address Address VARCHAR(300) DEFAULT NULL, CHANGE Telephone Telephone VARCHAR(100) DEFAULT NULL, CHANGE MobilePhone MobilePhone VARCHAR(150) DEFAULT NULL, CHANGE Password Password VARCHAR(64) DEFAULT NULL, CHANGE Email Email VARCHAR(60) DEFAULT NULL, CHANGE Viber Viber VARCHAR(100) DEFAULT NULL, CHANGE WebSite WebSite VARCHAR(250) DEFAULT NULL, CHANGE Facebook Facebook VARCHAR(250) DEFAULT NULL, CHANGE ContactEmail ContactEmail VARCHAR(250) DEFAULT NULL, CHANGE token token VARCHAR(100) DEFAULT NULL, CHANGE token_valid token_valid DATETIME DEFAULT NULL, CHANGE CityId CityId INT DEFAULT NULL;
     ALTER TABLE usertosocialnetwork CHANGE Image Image VARCHAR(500) DEFAULT NULL, CHANGE UserId UserId INT DEFAULT NULL;
     ALTER TABLE reviews CHANGE Title Title VARCHAR(300) DEFAULT NULL, CHANGE NickName NickName VARCHAR(150) DEFAULT NULL, CHANGE UserId UserId INT DEFAULT NULL, CHANGE IsActive IsActive TINYINT(1) DEFAULT NULL, CHANGE SysCreatedTime SysCreatedTime DATETIME DEFAULT NULL, CHANGE AdsId AdsId INT DEFAULT NULL;
     ALTER TABLE stars CHANGE ReviewId ReviewId INT DEFAULT NULL;
     ALTER TABLE adsadditionalinfo CHANGE CapacityMin CapacityMin INT DEFAULT NULL, CHANGE CapacityMax CapacityMax INT DEFAULT NULL, CHANGE AdsId AdsId INT DEFAULT NULL;
     ALTER TABLE ads ADD lat DOUBLE PRECISION DEFAULT NULL, ADD lng DOUBLE PRECISION DEFAULT NULL, ADD address VARCHAR(255) DEFAULT NULL, CHANGE PrePriceTo PrePriceTo INT DEFAULT NULL, CHANGE PriceTo PriceTo INT DEFAULT NULL, CHANGE PostPriceTo PostPriceTo INT DEFAULT NULL, CHANGE SysCreatedUserId SysCreatedUserId INT DEFAULT NULL, CHANGE SysModifyUserId SysModifyUserId INT DEFAULT NULL, CHANGE CategoryId CategoryId INT DEFAULT NULL, CHANGE CityId CityId INT DEFAULT NULL;
     ALTER TABLE ads_payed_date CHANGE ads ads INT DEFAULT NULL;
     ALTER TABLE city CHANGE ZipCode ZipCode VARCHAR(50) DEFAULT NULL, CHANGE show_in_home show_in_home TINYINT(1) DEFAULT NULL;
     ALTER TABLE emails CHANGE sysCreatedUTC sysCreatedUTC DATETIME DEFAULT NULL;
     ALTER TABLE eventtype CHANGE SysCreatorId SysCreatorId INT DEFAULT NULL, CHANGE SysModifierId SysModifierId INT DEFAULT NULL;
     ALTER TABLE tag ADD slug VARCHAR(255) DEFAULT NULL, CHANGE tag_type_id tag_type_id INT DEFAULT NULL;
     ALTER TABLE usertorole CHANGE UserId UserId INT DEFAULT NULL, CHANGE RoleId RoleId INT DEFAULT NULL;
        ");
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
