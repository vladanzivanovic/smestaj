<?php

declare(strict_types=1);

namespace SiteBundle\Parser\Resolver;

use SiteBundle\Dto\Ads\AdsInsertRequest;
use SiteBundle\Parser\AdsInsertRequestParser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class AdsInsertRequestResolver implements ValueResolverInterface
{
    public function __construct(
        private readonly AdsInsertRequestParser $parser,
    ) {
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (AdsInsertRequest::class !== $argument->getType()) {
            return [];
        }

        yield $this->parser->fromRequest($request);
    }
}
