<?php declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210717182613 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $users = $this->connection->fetchAllAssociative("
            select users.*, r.Code as roleCode from users
                join usertorole as utr on utr.UserId = users.id
                join role r on r.id = utr.RoleId
            WHERE users.email is not null and
                users.contact_email is not null
        ");

        $users2 = $this->connection->fetchAllAssociative("
            select users.*, r.Code as roleCode from users
                join ads a on users.Id = a.UserId
                join usertorole as utr on utr.UserId = users.id
                join role r on r.id = utr.RoleId
            group by a.UserId
            having count('a.*') > 1;
        ");

        foreach ($users2 as $user) {
            $users[] = $user;
        }

        $role = $this->connection->fetchAssociative("select * from role where Code = 'ROLE_CONTACT'");

        foreach ($users as $user) {
            $ads = $this->connection->fetchAllAssociative("
                select * from ads
                where UserId = :user
            ", ['user' => $user['Id']]);

            foreach ($ads as $ad) {
                $clonedUser = $user;
                $clonedUser['discr'] = 'contact';

                unset(
                    $clonedUser['Id'],
                    $clonedUser['email'],
                    $clonedUser['password'],
                    $clonedUser['UserId'],
                    $clonedUser['RoleId'],
                    $clonedUser['Name'],
                    $clonedUser['roleCode'],
                    $clonedUser['id']
                );

                $this->connection->insert(
                    'users',
                    $clonedUser
                );

                $contactId = $this->connection->lastInsertId();

                $this->connection->insert(
                    'usertorole',
                    [
                        'UserId' => $contactId,
                        'RoleId' => $role['id'],
                    ]
                );

                $this->connection->update(
                    'ads',
                    [
                        'UserId' => $contactId,
                        'owner_id' => $user['Id'],
                    ],
                    ['Id' => $ad['Id']]
                );
            }
            $discriminator = 'user';

            if ('ROLE_CONTACT' === $user['roleCode']) {
                $discriminator = 'contact';
            }

            $this->connection->update(
                'users',
                [
                    'contact_email' => null,
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
