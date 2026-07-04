<?php

declare(strict_types=1);

namespace SiteBundle\Tests\Dto\User;

use PHPUnit\Framework\TestCase;
use SiteBundle\Dto\User\RegisterUserRequest;

final class RegisterUserRequestTest extends TestCase
{
    public function testDefaultsMatchHandlerExpectedShape(): void
    {
        $dto = new RegisterUserRequest();

        self::assertSame('', $dto->email);
        self::assertSame('', $dto->password);
        self::assertSame('', $dto->firstname);
        self::assertSame('', $dto->lastname);
        self::assertSame('', $dto->repassword);
        self::assertNull($dto->facebookId);
        self::assertNull($dto->facebookImage);
    }

    public function testPropertiesAreAssignable(): void
    {
        $dto = new RegisterUserRequest();

        $dto->email = 'user@example.com';
        $dto->password = 'secret123';
        $dto->firstname = 'First';
        $dto->lastname = 'Last';
        $dto->repassword = 'secret123';
        $dto->facebookId = 'fb-123';
        $dto->facebookImage = 'https://fb.example/img.png';

        self::assertSame('user@example.com', $dto->email);
        self::assertSame('secret123', $dto->password);
        self::assertSame('First', $dto->firstname);
        self::assertSame('Last', $dto->lastname);
        self::assertSame('secret123', $dto->repassword);
        self::assertSame('fb-123', $dto->facebookId);
        self::assertSame('https://fb.example/img.png', $dto->facebookImage);
    }

    public function testFacebookIdHasNoConstraints(): void
    {
        $names = $this->collectAssertAttributeNames('facebookId');

        self::assertSame([], $names);
    }

    public function testFacebookImageHasNoConstraints(): void
    {
        $names = $this->collectAssertAttributeNames('facebookImage');

        self::assertSame([], $names);
    }

    public function testNoPropertyCarriesAnyAssertConstraint(): void
    {
        $properties = ['email', 'password', 'firstname', 'lastname', 'repassword', 'facebookId', 'facebookImage'];

        foreach ($properties as $property) {
            self::assertSame(
                [],
                $this->collectAssertAttributeNames($property),
                sprintf('Property %s must carry no Symfony Assert constraints (validation owned by User entity Registration group).', $property),
            );
        }
    }

    /**
     * @return array<int, class-string>
     */
    private function collectAssertAttributeNames(string $property): array
    {
        $reflection = new \ReflectionProperty(RegisterUserRequest::class, $property);

        $names = [];

        foreach ($reflection->getAttributes() as $attribute) {
            $name = $attribute->getName();

            if (true === str_starts_with($name, 'Symfony\\Component\\Validator\\Constraints\\')) {
                $names[] = $name;
            }
        }

        return $names;
    }
}
