<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Gedmo\Sluggable\Util\Urlizer;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210526085250 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $tags = $this->connection->executeQuery("SELECT * FROM `tag`")->fetchAllAssociative();

        foreach ($tags as $tag) {

            $slug = Urlizer::transliterate($tag['name']);

            $stmt = $this->connection->prepare("UPDATE tag SET slug = :slug WHERE id = :id");
            $stmt->bindValue('slug', $slug);
            $stmt->bindValue('id', $tag['id']);

            $stmt->execute();
        }
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
