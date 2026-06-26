<?php

declare(strict_types=1);

namespace SiteBundle\Parser\Resolver;

use SiteBundle\Dto\Ads\AdsListRequest;
use SiteBundle\Parser\AdsListRequestParser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class AdsListRequestResolver implements ValueResolverInterface
{
    public function __construct(
        private readonly AdsListRequestParser $parser,
    ) {
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (AdsListRequest::class !== $argument->getType()) {
            return [];
        }

        $extraParams = $request->attributes->get('extraParams');

        yield $this->parser->fromRequest(
            $request,
            \is_string($extraParams) ? $extraParams : null,
        );
    }
}
