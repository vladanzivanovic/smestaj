<?php

declare(strict_types=1);

namespace SiteBundle\Tests\Dto\Ads;

use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use SiteBundle\Dto\Ads\AdsSaveRequest;
use SiteBundle\Dto\Embedded\AdsContactDto;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AttributeLoader;
use Symfony\Component\Serializer\NameConverter\MetadataAwareNameConverter;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Merges the intent of the retired AdsInsertRequestTest + AdsUpdateRequestTest.
 * The two paired DTOs carried byte-for-byte identical wire shapes and Assert
 * profiles — one consolidated test covers both endpoints. The `?Ads` entity
 * discriminator lives on the parser signature (see AdsSaveRequestParserTest),
 * not on the DTO shape.
 */
final class AdsSaveRequestTest extends TestCase
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

    public function testDefaultsInitialiseEmbeddedContactAndScalarZeros(): void
    {
        $dto = new AdsSaveRequest();

        self::assertSame('', $dto->csrfToken);
        self::assertSame('', $dto->title);
        self::assertSame('', $dto->description);
        self::assertSame('', $dto->address);
        self::assertSame(0, $dto->category);
        self::assertSame('', $dto->city);
        self::assertSame(0.0, $dto->lat);
        self::assertSame(0.0, $dto->lng);
        self::assertSame(0, $dto->postPriceFrom);
        self::assertSame(0, $dto->postPriceTo);
        self::assertSame(0, $dto->prePriceFrom);
        self::assertSame(0, $dto->prePriceTo);
        self::assertSame(0, $dto->priceFrom);
        self::assertSame(0, $dto->priceTo);
        self::assertNull($dto->facebook);
        self::assertNull($dto->website);
        self::assertNull($dto->instagram);
        self::assertSame('[]', $dto->youtube);
        self::assertSame('[]', $dto->documents);
        self::assertSame([], $dto->tags);
        self::assertSame(0, $dto->pricePlan);
        self::assertNull($dto->paymentDate);
        self::assertInstanceOf(AdsContactDto::class, $dto->contact);
    }

    public function testDenormalizesFromFormShapedArrayIntoTypedProperties(): void
    {
        $payload = [
            '_csrf_token' => 'csrf-abc',
            'title_rs' => 'Villa Kopaonik',
            'description_rs' => 'Cozy cabin on the slope.',
            '_address' => 'Konaci 12',
            'category' => 3,
            'city' => 'Kopaonik',
            'lat' => 43.28,
            'lng' => 20.81,
            'post_price_from' => 100,
            'post_price_to' => 150,
            'pre_price_from' => 60,
            'pre_price_to' => 90,
            'price_from' => 80,
            'price_to' => 120,
            'facebook' => 'https://facebook.example/villa',
            'website' => 'https://villa.example',
            'instagram' => 'https://instagram.example/villa',
            'youtube' => '["https://youtu.be/abc"]',
            'documents' => '[{"id":1,"alt":"cover"}]',
            'tags' => ['size' => [1 => 'M']],
            'price_plan' => 2,
            'payment_date' => '2025-08-01',
            'contact' => [
                'first_name' => 'Pera',
                'surname' => 'Perić',
                'email' => 'pera@example.com',
                'telephone' => '+38160000000',
                'mobile_phone' => '+38160000001',
                'viber' => '+38160000002',
                'address' => 'Konaci 12',
                'city' => 'Kopaonik',
            ],
        ];

        $dto = $this->serializer->denormalize($payload, AdsSaveRequest::class);

        self::assertSame('csrf-abc', $dto->csrfToken);
        self::assertSame('Villa Kopaonik', $dto->title);
        self::assertSame('Cozy cabin on the slope.', $dto->description);
        self::assertSame('Konaci 12', $dto->address);
        self::assertSame(3, $dto->category);
        self::assertSame('Kopaonik', $dto->city);
        self::assertSame(43.28, $dto->lat);
        self::assertSame(20.81, $dto->lng);
        self::assertSame(100, $dto->postPriceFrom);
        self::assertSame(150, $dto->postPriceTo);
        self::assertSame(60, $dto->prePriceFrom);
        self::assertSame(90, $dto->prePriceTo);
        self::assertSame(80, $dto->priceFrom);
        self::assertSame(120, $dto->priceTo);
        self::assertSame('https://facebook.example/villa', $dto->facebook);
        self::assertSame('https://villa.example', $dto->website);
        self::assertSame('https://instagram.example/villa', $dto->instagram);
        self::assertSame('["https://youtu.be/abc"]', $dto->youtube);
        self::assertSame('[{"id":1,"alt":"cover"}]', $dto->documents);
        self::assertSame(['size' => [1 => 'M']], $dto->tags);
        self::assertSame(2, $dto->pricePlan);
        self::assertSame('2025-08-01', $dto->paymentDate);
        self::assertInstanceOf(AdsContactDto::class, $dto->contact);
        self::assertSame('Pera', $dto->contact->firstName);
        self::assertSame('Perić', $dto->contact->surname);
        self::assertSame('pera@example.com', $dto->contact->email);
        self::assertSame('+38160000000', $dto->contact->telephone);
        self::assertSame('+38160000001', $dto->contact->mobilePhone);
        self::assertSame('+38160000002', $dto->contact->viber);
        self::assertSame('Konaci 12', $dto->contact->address);
        self::assertSame('Kopaonik', $dto->contact->city);
    }

    public function testCsrfTokenCarriesNotBlankAndSerializedNameCsrfWireKey(): void
    {
        $property = new ReflectionProperty(AdsSaveRequest::class, 'csrfToken');

        self::assertCount(1, $property->getAttributes(Assert\NotBlank::class));

        $serializedName = $property->getAttributes(SerializedName::class);
        self::assertCount(1, $serializedName);
        self::assertSame(['_csrf_token'], $serializedName[0]->getArguments());
    }

    public function testContactCarriesAssertValidCascade(): void
    {
        $property = new ReflectionProperty(AdsSaveRequest::class, 'contact');

        self::assertCount(1, $property->getAttributes(Assert\Valid::class));
    }

    public function testWireKeyMappingsPreservedViaSerializedName(): void
    {
        $expectedMappings = [
            'title' => 'title_rs',
            'description' => 'description_rs',
            'address' => '_address',
            'postPriceFrom' => 'post_price_from',
            'postPriceTo' => 'post_price_to',
            'prePriceFrom' => 'pre_price_from',
            'prePriceTo' => 'pre_price_to',
            'priceFrom' => 'price_from',
            'priceTo' => 'price_to',
            'pricePlan' => 'price_plan',
            'paymentDate' => 'payment_date',
        ];

        foreach ($expectedMappings as $propertyName => $wireKey) {
            $property = new ReflectionProperty(AdsSaveRequest::class, $propertyName);
            $attributes = $property->getAttributes(SerializedName::class);

            self::assertCount(1, $attributes, sprintf('property %s missing #[SerializedName]', $propertyName));
            self::assertSame([$wireKey], $attributes[0]->getArguments(), sprintf('property %s wire key mismatch', $propertyName));
        }
    }

    /**
     * Locks the wire contract for the exact form body captured in
     * docs/CONTEXT_SPEC.md: after the Twig template restore of the
     * `_csrf_token` hidden input, the full spec body (parsed as
     * application/x-www-form-urlencoded) must denormalise into every
     * DTO scalar the parser depends on, including the CSRF token.
     * Complements testCsrfTokenCarriesNotBlankAndSerializedNameCsrfWireKey,
     * which only asserts the attribute wiring, not the round-trip.
     *
     * Numeric fields are int-cast after `parse_str` because Symfony's
     * RequestPayloadValueResolver forces `format=csv` to coerce string
     * scalars from form data (see
     * vendor/symfony/http-kernel/Controller/ArgumentResolver/RequestPayloadValueResolver.php);
     * this unit test skips that HttpKernel plumbing and coerces
     * manually so the same-shape denormalisation is exercised without a
     * kernel boot.
     */
    public function testDenormalizesWithExactSpecPayloadIncludingCsrfToken(): void
    {
        $body = '_csrf_token=sample-token'
            . '&title_rs=Alexander+Scriabin+%3A+Fantasy+for+two+pianos%2C+Francis+Poulenc+%3A+Concerto+for+Two+Pianos+and+Orchestra'
            . '&category=1'
            . '&place=Budva'
            . '&_address=4+Jula+5'
            . '&lat=42.2888931'
            . '&lng=18.8427722'
            . '&pre_price_from=1'
            . '&pre_price_to=2'
            . '&price_from=3'
            . '&price_to=4'
            . '&post_price_from=5'
            . '&post_price_to=6'
            . '&description_rs=%3Cp%3Edv+sfvsdd%3C%2Fp%3E'
            . '&tags%5Baccomodation-type%5D%5B3%5D=1'
            . '&tags%5Baccomodation%5D%5B7%5D=1'
            . '&tags%5Bfood%5D%5B24%5D=1'
            . '&tags%5Brange%5D%5B27%5D=123'
            . '&tags%5Brange%5D%5B28%5D=234'
            . '&tags%5Brange%5D%5B29%5D=2500'
            . '&contact%5Bfirst_name%5D=Vladan'
            . '&contact%5Bsurname%5D=Zivanovic'
            . '&contact%5Bemail%5D=vladapoz85%40gmail.com'
            . '&contact%5Baddress%5D=Cede+Vasovica'
            . '&contact%5Bcity%5D=Budva'
            . '&contact%5Btelephone%5D=0600550362'
            . '&contact%5Bmobile_phone%5D=0600550362'
            . '&price_plan=10'
            . '&documents=%5B%5D'
            . '&youtube=%5B%5D'
            . '&city=budva'
            . '&category=1'
            . '&contact%5Bcity%5D=budva'
            . '&additional_info=%5B%5D';

        parse_str($body, $payload);

        foreach (['category', 'pre_price_from', 'pre_price_to', 'price_from', 'price_to', 'post_price_from', 'post_price_to', 'price_plan'] as $intKey) {
            $payload[$intKey] = (int) $payload[$intKey];
        }
        $payload['lat'] = (float) $payload['lat'];
        $payload['lng'] = (float) $payload['lng'];

        $dto = $this->serializer->denormalize($payload, AdsSaveRequest::class);

        self::assertSame('sample-token', $dto->csrfToken);
        self::assertSame('Alexander Scriabin : Fantasy for two pianos, Francis Poulenc : Concerto for Two Pianos and Orchestra', $dto->title);
        self::assertSame('<p>dv sfvsdd</p>', $dto->description);
        self::assertSame('4 Jula 5', $dto->address);
        self::assertSame(1, $dto->category);
        self::assertSame('budva', $dto->city);
        self::assertSame(42.2888931, $dto->lat);
        self::assertSame(18.8427722, $dto->lng);
        self::assertSame(1, $dto->prePriceFrom);
        self::assertSame(2, $dto->prePriceTo);
        self::assertSame(3, $dto->priceFrom);
        self::assertSame(4, $dto->priceTo);
        self::assertSame(5, $dto->postPriceFrom);
        self::assertSame(6, $dto->postPriceTo);
        self::assertSame('[]', $dto->documents);
        self::assertSame('[]', $dto->youtube);
        self::assertSame(
            [
                'accomodation-type' => [3 => '1'],
                'accomodation' => [7 => '1'],
                'food' => [24 => '1'],
                'range' => [27 => '123', 28 => '234', 29 => '2500'],
            ],
            $dto->tags,
        );
        self::assertSame(10, $dto->pricePlan);
        self::assertInstanceOf(AdsContactDto::class, $dto->contact);
        self::assertSame('Vladan', $dto->contact->firstName);
        self::assertSame('Zivanovic', $dto->contact->surname);
        self::assertSame('vladapoz85@gmail.com', $dto->contact->email);
        self::assertSame('Cede Vasovica', $dto->contact->address);
        self::assertSame('budva', $dto->contact->city);
        self::assertSame('0600550362', $dto->contact->telephone);
        self::assertSame('0600550362', $dto->contact->mobilePhone);
    }
}
