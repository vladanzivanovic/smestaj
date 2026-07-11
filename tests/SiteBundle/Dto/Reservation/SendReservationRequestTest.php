<?php

declare(strict_types=1);

namespace SiteBundle\Tests\Dto\Reservation;

use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use SiteBundle\Dto\Reservation\SendReservationRequest;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

final class SendReservationRequestTest extends TestCase
{
    public function testDefaultsMatchHandlerExpectedShape(): void
    {
        $dto = new SendReservationRequest();

        self::assertSame('', $dto->csrfToken);
        self::assertSame('', $dto->firstname);
        self::assertSame('', $dto->lastname);
        self::assertSame('', $dto->email);
        self::assertSame('', $dto->mobile);
        self::assertNull($dto->viber);
        self::assertNull($dto->note);
        self::assertSame('', $dto->adultnumber);
        self::assertNull($dto->childrennumber);
        self::assertSame('', $dto->checkin);
        self::assertSame('', $dto->checkout);
        self::assertNull($dto->notificationtype);
    }

    public function testPropertiesAreAssignable(): void
    {
        $dto = new SendReservationRequest();

        $dto->csrfToken = 'csrf-abc';
        $dto->firstname = 'Pera';
        $dto->lastname = 'Perić';
        $dto->email = 'pera@example.com';
        $dto->mobile = '+38160000000';
        $dto->viber = '+38160000001';
        $dto->note = 'Test note';
        $dto->adultnumber = '2';
        $dto->childrennumber = '1';
        $dto->checkin = '15.07.2025';
        $dto->checkout = '20.07.2025';
        $dto->notificationtype = '2';

        self::assertSame('csrf-abc', $dto->csrfToken);
        self::assertSame('Pera', $dto->firstname);
        self::assertSame('Perić', $dto->lastname);
        self::assertSame('pera@example.com', $dto->email);
        self::assertSame('+38160000000', $dto->mobile);
        self::assertSame('+38160000001', $dto->viber);
        self::assertSame('Test note', $dto->note);
        self::assertSame('2', $dto->adultnumber);
        self::assertSame('1', $dto->childrennumber);
        self::assertSame('15.07.2025', $dto->checkin);
        self::assertSame('20.07.2025', $dto->checkout);
        self::assertSame('2', $dto->notificationtype);
    }

    public function testCsrfTokenCarriesNotBlankAndSerializedNameTokenWireKey(): void
    {
        $property = new ReflectionProperty(SendReservationRequest::class, 'csrfToken');

        self::assertCount(1, $property->getAttributes(Assert\NotBlank::class));

        $serializedName = $property->getAttributes(SerializedName::class);
        self::assertCount(1, $serializedName);
        self::assertSame(['token'], $serializedName[0]->getArguments());
    }

    public function testFirstnameHasNotBlankAndLengthConstraints(): void
    {
        $names = $this->collectAssertAttributeNames('firstname');

        self::assertContains(Assert\NotBlank::class, $names);
        self::assertContains(Assert\Length::class, $names);
    }

    public function testLastnameHasNotBlankAndLengthConstraints(): void
    {
        $names = $this->collectAssertAttributeNames('lastname');

        self::assertContains(Assert\NotBlank::class, $names);
        self::assertContains(Assert\Length::class, $names);
    }

    public function testEmailHasNotBlankEmailAndLengthConstraints(): void
    {
        $names = $this->collectAssertAttributeNames('email');

        self::assertContains(Assert\NotBlank::class, $names);
        self::assertContains(Assert\Email::class, $names);
        self::assertContains(Assert\Length::class, $names);
    }

    public function testMobileHasNotBlankAndLengthConstraints(): void
    {
        $names = $this->collectAssertAttributeNames('mobile');

        self::assertContains(Assert\NotBlank::class, $names);
        self::assertContains(Assert\Length::class, $names);
    }

    public function testViberHasOnlyLengthConstraintAndNoNotBlank(): void
    {
        $names = $this->collectAssertAttributeNames('viber');

        self::assertContains(Assert\Length::class, $names);
        self::assertNotContains(Assert\NotBlank::class, $names);
    }

    public function testNoteHasNoConstraints(): void
    {
        $names = $this->collectAssertAttributeNames('note');

        self::assertSame([], $names);
    }

    public function testAdultnumberHasNotBlankConstraint(): void
    {
        $names = $this->collectAssertAttributeNames('adultnumber');

        self::assertContains(Assert\NotBlank::class, $names);
    }

    public function testChildrennumberHasNoConstraints(): void
    {
        $names = $this->collectAssertAttributeNames('childrennumber');

        self::assertSame([], $names);
    }

    public function testCheckinHasNotBlankConstraint(): void
    {
        $names = $this->collectAssertAttributeNames('checkin');

        self::assertContains(Assert\NotBlank::class, $names);
    }

    public function testCheckoutHasNotBlankConstraint(): void
    {
        $names = $this->collectAssertAttributeNames('checkout');

        self::assertContains(Assert\NotBlank::class, $names);
    }

    public function testNotificationtypeHasNotBlankConstraint(): void
    {
        $names = $this->collectAssertAttributeNames('notificationtype');

        self::assertContains(Assert\NotBlank::class, $names);
    }

    /**
     * @return array<int, class-string>
     */
    private function collectAssertAttributeNames(string $property): array
    {
        $reflection = new ReflectionProperty(SendReservationRequest::class, $property);

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
