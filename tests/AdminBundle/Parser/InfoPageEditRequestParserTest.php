<?php

declare(strict_types=1);

namespace AdminBundle\Tests\Parser;

use AdminBundle\Parser\InfoPageEditRequestParser;
use PHPUnit\Framework\TestCase;
use SiteBundle\Entity\AdsInfoPage;
use SiteBundle\Services\InfoPage\HouseRulesHtmlSanitizer;
use Symfony\Component\HttpFoundation\ParameterBag;

final class InfoPageEditRequestParserTest extends TestCase
{
    public function testUploadedImagesJsonProducesImageStates(): void
    {
        $parser = new InfoPageEditRequestParser(new HouseRulesHtmlSanitizer(new \HTMLPurifier()));
        $bag = new ParameterBag([
            'uploadedImages' => json_encode([
                ['id' => 7, 'isMain' => true, 'fileName' => 'a.jpg'],
                ['id' => null, 'isMain' => false, 'fileName' => 'b.jpg'],
            ]),
        ]);
        $entity = new AdsInfoPage();

        $model = $parser->parse($bag, $entity);

        self::assertSame(
            [
                ['id' => 7, 'isMain' => true, 'fileName' => 'a.jpg'],
                ['id' => null, 'isMain' => false, 'fileName' => 'b.jpg'],
            ],
            $model->imageStates,
        );
    }

    public function testMalformedUploadedImagesYieldsEmptyImageStates(): void
    {
        $parser = new InfoPageEditRequestParser(new HouseRulesHtmlSanitizer(new \HTMLPurifier()));
        $bag = new ParameterBag(['uploadedImages' => 'not-json']);
        $entity = new AdsInfoPage();

        $model = $parser->parse($bag, $entity);

        self::assertSame([], $model->imageStates);
        self::assertArrayNotHasKey('images', $model->parserViolations);
    }
}
