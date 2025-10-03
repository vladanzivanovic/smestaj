<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210717214536 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $users = $this->connection->fetchAllAssociative("
            select users.*, r.Code as roleCode from users
                join usertorole as utr on utr.UserId = users.id
                join role r on r.id = utr.RoleId
            WHERE users.discr = ''
        ");

        foreach ($users as $user) {
            $discriminator = 'user';

            if ('ROLE_CONTACT' === $user['roleCode']) {
                $discriminator = 'contact';
            }

            $this->connection->update(
                'users',
                [
                    'discr' => $discriminator,
                ],
                ['Id' => $user['Id']]
            );
        }
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
