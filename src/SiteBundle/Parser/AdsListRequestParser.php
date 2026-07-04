<?php

declare(strict_types=1);

namespace SiteBundle\Parser;

use SiteBundle\Dto\Ads\AdsListRequest;
use Symfony\Component\HttpFoundation\Request;

final class AdsListRequestParser
{
    public function fromRequest(Request $request, ?string $extraParams): AdsListRequest
    {
        return new AdsListRequest($request->query, $extraParams);
    }
}
