<?php

declare(strict_types=1);

namespace SiteBundle\Parser\Resolver;

use SiteBundle\Dto\Ads\AdsUpdateRequest;
use SiteBundle\Parser\AdsUpdateRequestParser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class AdsUpdateRequestResolver implements ValueResolverInterface
{
    public function __construct(
        private readonly AdsUpdateRequestParser $parser,
    ) {
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (AdsUpdateRequest::class !== $argument->getType()) {
            return [];
        }

        yield $this->parser->fromRequest($request);
    }
}
