<?php

declare(strict_types=1);

namespace SiteBundle\Tests\Services\InfoPage;

use PHPUnit\Framework\TestCase;
use SiteBundle\Entity\AdsInfoPage;
use SiteBundle\Entity\AdsInfoPageImage;
use SiteBundle\Services\InfoPage\AdsInfoPageMainImageValidator;

final class AdsInfoPageMainImageValidatorTest extends TestCase
{
    public function testEmptyImageCollectionReturnsNoViolations(): void
    {
        $validator = new AdsInfoPageMainImageValidator();
        $entity = new AdsInfoPage();

        self::assertSame([], $validator->validate($entity));
    }

    public function testExactlyOneMainImageReturnsNoViolations(): void
    {
        $validator = new AdsInfoPageMainImageValidator();
        $entity = new AdsInfoPage();

        $main = new AdsInfoPageImage();
        $main->setIsMain(true);
        $entity->addImage($main);

        $other = new AdsInfoPageImage();
        $other->setIsMain(false);
        $entity->addImage($other);

        self::assertSame([], $validator->validate($entity));
    }

    public function testZeroMainImagesOnNonEmptyPageReturnsViolation(): void
    {
        $validator = new AdsInfoPageMainImageValidator();
        $entity = new AdsInfoPage();

        $first = new AdsInfoPageImage();
        $first->setIsMain(false);
        $entity->addImage($first);

        $second = new AdsInfoPageImage();
        $second->setIsMain(false);
        $entity->addImage($second);

        $result = $validator->validate($entity);

        self::assertArrayHasKey('images', $result);
        self::assertSame('Tačno jedna slika mora biti označena kao glavna.', $result['images']);
    }

    public function testTwoMainImagesReturnsViolation(): void
    {
        $validator = new AdsInfoPageMainImageValidator();
        $entity = new AdsInfoPage();

        $first = new AdsInfoPageImage();
        $first->setIsMain(true);
        $entity->addImage($first);

        $second = new AdsInfoPageImage();
        $second->setIsMain(true);
        $entity->addImage($second);

        $result = $validator->validate($entity);

        self::assertArrayHasKey('images', $result);
        self::assertSame('Tačno jedna slika mora biti označena kao glavna.', $result['images']);
    }
}
