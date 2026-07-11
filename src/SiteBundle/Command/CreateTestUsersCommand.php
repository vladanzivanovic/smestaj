<?php

declare(strict_types=1);

namespace SiteBundle\Command;

use SiteBundle\Entity\EntityStatusInterface;
use SiteBundle\Entity\Role;
use SiteBundle\Entity\User;
use SiteBundle\Handler\RoleHandler;
use SiteBundle\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-test-users',
    description: 'Idempotently create the two sanctioned QA test users (siteuser@test.com + adminuser@test.com, password Test1234.).',
)]
final class CreateTestUsersCommand extends Command
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly RoleHandler $roleHandler,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $fixtures = [
            'siteuser@test.com' => [
                'firstname' => 'Site',
                'lastname' => 'Tester',
                'role' => Role::ROLE_ADVANCED_USER,
            ],
            'adminuser@test.com' => [
                'firstname' => 'Admin',
                'lastname' => 'Tester',
                'role' => Role::ROLE_ADMIN,
            ],
        ];

        foreach ($fixtures as $email => $spec) {
            $existing = $this->userRepository->findOneBy(['email' => $email]);

            if (null !== $existing) {
                $output->writeln(sprintf(
                    '<comment>User %s already exists (id=%d), skipping.</comment>',
                    $email,
                    $existing->getId(),
                ));

                continue;
            }

            $user = new User();
            $user->setEmail($email);
            $user->setFirstname($spec['firstname']);
            $user->setLastname($spec['lastname']);
            $user->setPassword($this->passwordHasher->hashPassword($user, 'Test1234.'));
            $user->setStatus(EntityStatusInterface::STATUS_ACTIVE);

            $this->roleHandler->setUserToRole($user, $spec['role']);

            $this->userRepository->persist($user);
            $this->userRepository->flush();

            $output->writeln(sprintf(
                '<info>User %s created (id=%d, role=%s).</info>',
                $email,
                $user->getId(),
                $spec['role'],
            ));
        }

        return Command::SUCCESS;
    }
}
