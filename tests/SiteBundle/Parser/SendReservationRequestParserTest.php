<?php

declare(strict_types=1);

namespace SiteBundle\Tests\Parser;

use PHPUnit\Framework\TestCase;
use SiteBundle\Dto\Reservation\SendReservationRequest;
use SiteBundle\Entity\Ads;
use SiteBundle\Parser\SendReservationRequestParser;

final class SendReservationRequestParserTest extends TestCase
{
    private SendReservationRequestParser $parser;

    private Ads $ads;

    protected function setUp(): void
    {
        $this->parser = new SendReservationRequestParser();
        $this->ads = new Ads();
    }

    public function testToArrayProducesHandlerKeyShapeForFullyPopulatedDto(): void
    {
        $dto = $this->buildFullDto();

        $data = $this->parser->toArray($dto, $this->ads);

        self::assertSame(
            [
                'firstname' => 'Pera',
                'lastname' => 'Perić',
                'email' => 'pera@example.com',
                'mobile' => '+38160000000',
                'viber' => '+38160000001',
                'note' => 'Test note',
                'adultnumber' => '2',
                'childrennumber' => '1',
                'checkin' => '15.07.2025',
                'checkout' => '20.07.2025',
                'notificationtype' => '2',
                'adsId' => $this->ads,
            ],
            $data,
        );
    }

    public function testToArrayInjectsAdsObjectUnderAdsIdKey(): void
    {
        $dto = $this->buildFullDto();

        $data = $this->parser->toArray($dto, $this->ads);

        self::assertArrayHasKey('adsId', $data);
        self::assertSame($this->ads, $data['adsId']);
    }

    public function testToArrayDoesNotIncludeTokenKey(): void
    {
        $dto = $this->buildFullDto();
        $dto->csrfToken = 'csrf-secret-value';

        $data = $this->parser->toArray($dto, $this->ads);

        self::assertArrayNotHasKey('token', $data);
        self::assertArrayNotHasKey('csrfToken', $data);
    }

    public function testToArrayDoesNotIncludeFormAdsidKey(): void
    {
        $dto = $this->buildFullDto();

        $data = $this->parser->toArray($dto, $this->ads);

        self::assertArrayNotHasKey('adsid', $data);
    }

    public function testToArrayCoercesEmptyOptionalStringsToNull(): void
    {
        $dto = $this->buildFullDto();
        $dto->viber = '';
        $dto->note = '';
        $dto->childrennumber = '';

        $data = $this->parser->toArray($dto, $this->ads);

        self::assertNull($data['viber']);
        self::assertNull($data['note']);
        self::assertNull($data['childrennumber']);
    }

    public function testToArrayPreservesNotificationtypeEvenWhenEmpty(): void
    {
        $dto = $this->buildFullDto();
        $dto->notificationtype = '';

        $data = $this->parser->toArray($dto, $this->ads);

        self::assertArrayHasKey('notificationtype', $data);
        self::assertSame('', $data['notificationtype']);
    }

    public function testToArrayCoercesEmptyRequiredFieldsToNullForLegacyParity(): void
    {
        $dto = $this->buildFullDto();
        $dto->firstname = '';
        $dto->lastname = '';
        $dto->email = '';

        $data = $this->parser->toArray($dto, $this->ads);

        self::assertNull($data['firstname']);
        self::assertNull($data['lastname']);
        self::assertNull($data['email']);
    }

    public function testToArrayHandlesAllNullOptionalDefaults(): void
    {
        $dto = new SendReservationRequest();
        $dto->firstname = 'F';
        $dto->lastname = 'L';
        $dto->email = 'a@b.c';
        $dto->mobile = '+381';
        $dto->adultnumber = '1';
        $dto->checkin = '01.01.2025';
        $dto->checkout = '02.01.2025';
        $dto->notificationtype = '0';

        $data = $this->parser->toArray($dto, $this->ads);

        self::assertNull($data['viber']);
        self::assertNull($data['note']);
        self::assertNull($data['childrennumber']);
        self::assertSame('0', $data['notificationtype']);
        self::assertSame($this->ads, $data['adsId']);
    }

    private function buildFullDto(): SendReservationRequest
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

        return $dto;
    }
}
