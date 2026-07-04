<?php

declare(strict_types=1);

namespace SiteBundle\Parser;

use SiteBundle\Dto\Ads\AdsUpdateRequest;
use Symfony\Component\HttpFoundation\Request;

final class AdsUpdateRequestParser
{
    public function fromRequest(Request $request): AdsUpdateRequest
    {
        $csrf = (string) $request->request->get('_csrf_token', '');

        return new AdsUpdateRequest($request->request, $csrf);
    }
}
