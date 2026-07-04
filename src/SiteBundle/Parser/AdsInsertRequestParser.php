<?php

declare(strict_types=1);

namespace SiteBundle\Parser;

use SiteBundle\Dto\Ads\AdsInsertRequest;
use Symfony\Component\HttpFoundation\Request;

final class AdsInsertRequestParser
{
    public function fromRequest(Request $request): AdsInsertRequest
    {
        $csrf = (string) $request->request->get('_csrf_token', '');

        return new AdsInsertRequest($request->request, $csrf);
    }
}
