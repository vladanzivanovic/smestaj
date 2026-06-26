<?php

declare(strict_types=1);

namespace SiteBundle\Tests\Dto\User;

use PHPUnit\Framework\TestCase;
use SiteBundle\Dto\User\SetNewPasswordRequest;
use Symfony\Component\Validator\Constraints as Assert;

final class SetNewPasswordRequestTest extends TestCase
{
    public function testDefaultValuesAreEmptyStrings(): void
    {
        $dto = new SetNewPasswordRequest();

        self::assertSame('', $dto->password);
        self::assertSame('', $dto->rePassword);
    }

    public function testPropertiesAreAssignable(): void
    {
        $dto = new SetNewPasswordRequest();

        $dto->password = 'newSecret';
        $dto->rePassword = 'newSecret';

        self::assertSame('newSecret', $dto->password);
        self::assertSame('newSecret', $dto->rePassword);
    }

    public function testPasswordHasNotBlankAndLengthConstraints(): void
    {
        $reflection = new \ReflectionProperty(SetNewPasswordRequest::class, 'password');

        $assertNames = $this->collectAssertAttributeNames($reflection);

        self::assertContains(Assert\NotBlank::class, $assertNames);
        self::assertContains(Assert\Length::class, $assertNames);
    }

    public function testRePasswordHasNotBlankConstraint(): void
    {
        $reflection = new \ReflectionProperty(SetNewPasswordRequest::class, 'rePassword');

        $assertNames = $this->collectAssertAttributeNames($reflection);

        self::assertContains(Assert\NotBlank::class, $assertNames);
    }

    /**
     * @return array<int, class-string>
     */
    private function collectAssertAttributeNames(\ReflectionProperty $reflection): array
    {
        $names = [];

        foreach ($reflection->getAttributes() as $attribute) {
            $attributeName = $attribute->getName();

            if (true === str_starts_with($attributeName, 'Symfony\\Component\\Validator\\Constraints\\')) {
                $names[] = $attributeName;
            }
        }

        return $names;
    }
}
