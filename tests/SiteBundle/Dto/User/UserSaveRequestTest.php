<?php

declare(strict_types=1);

namespace SiteBundle\Tests\Dto\User;

use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use SiteBundle\Dto\User\UserSaveRequest;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AttributeLoader;
use Symfony\Component\Serializer\NameConverter\MetadataAwareNameConverter;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Merges the intent of the two retired paired DTO tests. The
 * consolidated DTO uses validation groups to model the Insert-vs-Update
 * Assert divergence: the 5 shared fields carry `NotBlank(groups:
 * ['register'])` (fires only on register endpoint); `email` also carries
 * unconditional `Email`; `password` also carries unconditional
 * `Length(min: 6)`. The two facebook fields carry no constraints.
 */
final class UserSaveRequestTest extends TestCase
{
    private Serializer $serializer;

    protected function setUp(): void
    {
        $metadataFactory = new ClassMetadataFactory(new AttributeLoader());
        $nameConverter = new MetadataAwareNameConverter($metadataFactory);
        $objectNormalizer = new ObjectNormalizer(
            $metadataFactory,
            $nameConverter,
            null,
            new ReflectionExtractor(),
        );

        $this->serializer = new Serializer([new ArrayDenormalizer(), $objectNormalizer]);
    }

    public function testAllPropertiesDefaultToNullForPatchSemantics(): void
    {
        $dto = new UserSaveRequest();

        self::assertNull($dto->email);
        self::assertNull($dto->password);
        self::assertNull($dto->firstname);
        self::assertNull($dto->lastname);
        self::assertNull($dto->repassword);
        self::assertNull($dto->facebookId);
        self::assertNull($dto->facebookImage);
    }

    public function testDenormalizesFromFormShapedArrayIntoTypedProperties(): void
    {
        $payload = [
            'email' => 'user@example.com',
            'password' => 'secret123',
            'firstname' => 'First',
            'lastname' => 'Last',
            'repassword' => 'secret123',
            'facebookId' => 'fb-uid-42',
            'facebookImage' => 'https://fb.example/img.png',
        ];

        $dto = $this->serializer->denormalize($payload, UserSaveRequest::class);

        self::assertSame('user@example.com', $dto->email);
        self::assertSame('secret123', $dto->password);
        self::assertSame('First', $dto->firstname);
        self::assertSame('Last', $dto->lastname);
        self::assertSame('secret123', $dto->repassword);
        self::assertSame('fb-uid-42', $dto->facebookId);
        self::assertSame('https://fb.example/img.png', $dto->facebookImage);
    }

    public function testEmailCarriesUnconditionalEmailAndGroupedNotBlank(): void
    {
        $notBlank = $this->getSingleAttribute('email', Assert\NotBlank::class);
        self::assertSame(['register'], $notBlank->groups);

        $email = $this->getSingleAttribute('email', Assert\Email::class);
        self::assertSame(['Default'], $email->groups);
    }

    public function testPasswordCarriesUnconditionalLengthAndGroupedNotBlank(): void
    {
        $notBlank = $this->getSingleAttribute('password', Assert\NotBlank::class);
        self::assertSame(['register'], $notBlank->groups);

        $length = $this->getSingleAttribute('password', Assert\Length::class);
        self::assertSame(6, $length->min);
        self::assertSame(['Default'], $length->groups);
    }

    public function testFirstnameCarriesGroupedNotBlankOnly(): void
    {
        $notBlank = $this->getSingleAttribute('firstname', Assert\NotBlank::class);
        self::assertSame(['register'], $notBlank->groups);
    }

    public function testLastnameCarriesGroupedNotBlankOnly(): void
    {
        $notBlank = $this->getSingleAttribute('lastname', Assert\NotBlank::class);
        self::assertSame(['register'], $notBlank->groups);
    }

    public function testRepasswordCarriesGroupedNotBlankOnly(): void
    {
        $notBlank = $this->getSingleAttribute('repassword', Assert\NotBlank::class);
        self::assertSame(['register'], $notBlank->groups);
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

    /**
     * @template T of object
     * @param class-string<T> $attributeClass
     * @return T
     */
    private function getSingleAttribute(string $property, string $attributeClass): object
    {
        $reflection = new ReflectionProperty(UserSaveRequest::class, $property);
        $attributes = $reflection->getAttributes($attributeClass);

        self::assertCount(1, $attributes, sprintf('property %s missing #[%s]', $property, $attributeClass));

        return $attributes[0]->newInstance();
    }

    /**
     * @return array<int, class-string>
     */
    private function collectAssertAttributeNames(string $property): array
    {
        $reflection = new ReflectionProperty(UserSaveRequest::class, $property);

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
