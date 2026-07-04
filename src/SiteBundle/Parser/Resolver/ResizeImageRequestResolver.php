<?php

declare(strict_types=1);

namespace SiteBundle\Parser\Resolver;

use SiteBundle\Dto\Ads\ResizeImageRequest;
use SiteBundle\Parser\ResizeImageRequestParser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class ResizeImageRequestResolver implements ValueResolverInterface
{
    public function __construct(
        private readonly ResizeImageRequestParser $parser,
    ) {
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (ResizeImageRequest::class !== $argument->getType()) {
            return [];
        }

        yield $this->parser->fromRequest($request);
    }
}
