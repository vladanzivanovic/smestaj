<?php

declare(strict_types=1);

namespace SiteBundle\Tests\Dto;

use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use SiteBundle\Dto\ContactFormRequest;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AttributeLoader;
use Symfony\Component\Serializer\NameConverter\MetadataAwareNameConverter;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Constraints as Assert;

final class ContactFormRequestTest extends TestCase
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

    public function testDefaultValuesForEveryProperty(): void
    {
        $dto = new ContactFormRequest();

        self::assertSame('', $dto->name);
        self::assertSame('', $dto->email);
        self::assertSame('', $dto->subject);
        self::assertSame('', $dto->message);
        self::assertSame('', $dto->website);
    }

    public function testDenormalizesFromJsonShapedArrayIntoTypedProperties(): void
    {
        $payload = [
            'name' => 'Pera Perić',
            'email' => 'pera@example.com',
            'subject' => 'Hello',
            'message' => 'Some message body.',
            'website' => '',
        ];

        $dto = $this->serializer->denormalize($payload, ContactFormRequest::class);

        self::assertSame('Pera Perić', $dto->name);
        self::assertSame('pera@example.com', $dto->email);
        self::assertSame('Hello', $dto->subject);
        self::assertSame('Some message body.', $dto->message);
        self::assertSame('', $dto->website);
    }

    public function testNameCarriesNotBlankAndLength(): void
    {
        $property = new ReflectionProperty(ContactFormRequest::class, 'name');

        self::assertCount(1, $property->getAttributes(Assert\NotBlank::class));
        self::assertCount(1, $property->getAttributes(Assert\Length::class));
    }

    public function testEmailCarriesNotBlankEmailAndLength(): void
    {
        $property = new ReflectionProperty(ContactFormRequest::class, 'email');

        self::assertCount(1, $property->getAttributes(Assert\NotBlank::class));
        self::assertCount(1, $property->getAttributes(Assert\Email::class));
        self::assertCount(1, $property->getAttributes(Assert\Length::class));
    }

    public function testSubjectCarriesNotBlankAndLength(): void
    {
        $property = new ReflectionProperty(ContactFormRequest::class, 'subject');

        self::assertCount(1, $property->getAttributes(Assert\NotBlank::class));
        self::assertCount(1, $property->getAttributes(Assert\Length::class));
    }

    public function testMessageCarriesNotBlankAndLength(): void
    {
        $property = new ReflectionProperty(ContactFormRequest::class, 'message');

        self::assertCount(1, $property->getAttributes(Assert\NotBlank::class));
        self::assertCount(1, $property->getAttributes(Assert\Length::class));
    }

    public function testWebsitePropertyHasNoAssertConstraints(): void
    {
        $property = new ReflectionProperty(ContactFormRequest::class, 'website');

        $assertAttributes = [];

        foreach ($property->getAttributes() as $attribute) {
            $attributeName = $attribute->getName();

            if (true === str_starts_with($attributeName, 'Symfony\\Component\\Validator\\Constraints\\')) {
                $assertAttributes[] = $attributeName;
            }
        }

        self::assertSame([], $assertAttributes, 'website must have no Assert constraints (honeypot policy lives in controller).');
    }
}
