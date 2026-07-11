<?php

declare(strict_types=1);

namespace SiteBundle\Tests\Parser;

use PHPUnit\Framework\TestCase;
use SiteBundle\Dto\User\UserSaveRequest;
use SiteBundle\Entity\User;
use SiteBundle\Parser\UserSaveRequestParser;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Merges the intent of the two retired paired parser tests. The
 * consolidated parser accepts a nullable ?User discriminator on its
 * `parse` signature — null triggers the register path (fresh entity +
 * always-hash password), non-null triggers the update path (patch
 * present fields + rehash only if password was submitted). The two
 * typed `toArray*` methods preserve the divergent downstream contracts.
 */
final class UserSaveRequestParserTest extends TestCase
{
    private UserSaveRequestParser $parser;

    private UserPasswordHasherInterface $passwordHasher;

    protected function setUp(): void
    {
        $this->passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $this->passwordHasher
            ->method('hashPassword')
            ->willReturnCallback(static fn (User $user, string $plain): string => 'hashed:' . $plain);

        $this->parser = new UserSaveRequestParser($this->passwordHasher);
    }

    public function testParseCreatesFreshUserWithHashedPasswordOnRegisterPath(): void
    {
        $dto = $this->buildFullDto();

        $user = $this->parser->parse($dto);

        self::assertInstanceOf(User::class, $user);
        self::assertSame('user@example.com', $user->getEmail());
        self::assertSame('First', $user->getFirstname());
        self::assertSame('Last', $user->getLastname());
        self::assertSame('secret123', $user->getRepassword());
        self::assertSame('hashed:secret123', $user->getPassword());
    }

    public function testParseAppliesOnlySuppliedFieldsAndRehashesPasswordOnUpdatePath(): void
    {
        $dto = new UserSaveRequest();
        $dto->email = 'new@example.com';
        $dto->password = 'freshSecret';

        $user = $this->buildExistingUser();

        $result = $this->parser->parse($dto, $user);

        self::assertSame($user, $result);
        self::assertSame('new@example.com', $result->getEmail());
        self::assertSame('hashed:freshSecret', $result->getPassword());
        self::assertSame('OldFirst', $result->getFirstname());
        self::assertSame('OldLast', $result->getLastname());
        self::assertSame('old-repass', $result->getRepassword());
    }

    public function testParseLeavesUserUnchangedForAllNullDtoOnUpdatePath(): void
    {
        $dto = new UserSaveRequest();
        $user = $this->buildExistingUser();

        $result = $this->parser->parse($dto, $user);

        self::assertSame($user, $result);
        self::assertSame('old@example.com', $result->getEmail());
        self::assertSame('OldFirst', $result->getFirstname());
        self::assertSame('OldLast', $result->getLastname());
        self::assertSame('old-repass', $result->getRepassword());
        self::assertSame('old-hashed-password', $result->getPassword());
    }

    public function testToRegistrationArrayProducesFiveAlwaysPresentKeys(): void
    {
        $dto = $this->buildFullDto();

        $data = $this->parser->toRegistrationArray($dto);

        self::assertSame(
            [
                'email' => 'user@example.com',
                'password' => 'secret123',
                'firstname' => 'First',
                'lastname' => 'Last',
                'repassword' => 'secret123',
            ],
            $data,
        );
    }

    public function testToRegistrationArrayIncludesFacebookIdWhenSet(): void
    {
        $dto = $this->buildFullDto();
        $dto->facebookId = 'fb-uid-42';

        $data = $this->parser->toRegistrationArray($dto);

        self::assertArrayHasKey('facebookId', $data);
        self::assertSame('fb-uid-42', $data['facebookId']);
        self::assertArrayNotHasKey('facebookImage', $data);
    }

    public function testToRegistrationArrayIncludesFacebookImageWhenSet(): void
    {
        $dto = $this->buildFullDto();
        $dto->facebookId = 'fb-uid-42';
        $dto->facebookImage = 'https://example.com/p.png';

        $data = $this->parser->toRegistrationArray($dto);

        self::assertSame('https://example.com/p.png', $data['facebookImage']);
    }

    public function testToUpdateArrayReturnsEmptyForAllNullDto(): void
    {
        $dto = new UserSaveRequest();

        $data = $this->parser->toUpdateArray($dto);

        self::assertSame([], $data);
    }

    public function testToUpdateArrayIncludesOnlyNonNullPropertiesAndNeverFacebook(): void
    {
        $dto = new UserSaveRequest();
        $dto->firstname = 'NewFirst';
        $dto->email = 'new@example.com';
        $dto->facebookId = 'fb-should-be-ignored';
        $dto->facebookImage = 'https://example.com/ignored.png';

        $data = $this->parser->toUpdateArray($dto);

        self::assertSame(
            [
                'email' => 'new@example.com',
                'firstname' => 'NewFirst',
            ],
            $data,
        );
    }

    private function buildFullDto(): UserSaveRequest
    {
        $dto = new UserSaveRequest();
        $dto->email = 'user@example.com';
        $dto->password = 'secret123';
        $dto->firstname = 'First';
        $dto->lastname = 'Last';
        $dto->repassword = 'secret123';

        return $dto;
    }

    private function buildExistingUser(): User
    {
        $user = new User();
        $user->setEmail('old@example.com');
        $user->setFirstname('OldFirst');
        $user->setLastname('OldLast');
        $user->setRepassword('old-repass');
        $user->setPassword('old-hashed-password');

        return $user;
    }
}
