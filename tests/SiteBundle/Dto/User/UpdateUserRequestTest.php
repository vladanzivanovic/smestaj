<?php

declare(strict_types=1);

namespace SiteBundle\Tests\Dto\User;

use PHPUnit\Framework\TestCase;
use SiteBundle\Dto\User\UpdateUserRequest;

final class UpdateUserRequestTest extends TestCase
{
    public function testAllPropertiesDefaultToNullForPatchSemantics(): void
    {
        $dto = new UpdateUserRequest();

        self::assertNull($dto->email);
        self::assertNull($dto->password);
        self::assertNull($dto->firstname);
        self::assertNull($dto->lastname);
        self::assertNull($dto->repassword);
    }

    public function testPropertiesAreAssignable(): void
    {
        $dto = new UpdateUserRequest();

        $dto->email = 'user@example.com';
        $dto->password = 'secret123';
        $dto->firstname = 'First';
        $dto->lastname = 'Last';
        $dto->repassword = 'secret123';

        self::assertSame('user@example.com', $dto->email);
        self::assertSame('secret123', $dto->password);
        self::assertSame('First', $dto->firstname);
        self::assertSame('Last', $dto->lastname);
        self::assertSame('secret123', $dto->repassword);
    }

    public function testNoPropertyCarriesAnyAssertConstraint(): void
    {
        foreach (['email', 'password', 'firstname', 'lastname', 'repassword'] as $property) {
            self::assertSame(
                [],
                $this->collectAssertAttributeNames($property),
                sprintf('Property %s must carry no Symfony Assert constraints.', $property),
            );
        }
    }

    /**
     * @return array<int, class-string>
     */
    private function collectAssertAttributeNames(string $property): array
    {
        $reflection = new \ReflectionProperty(UpdateUserRequest::class, $property);

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
