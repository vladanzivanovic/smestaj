<?php

declare(strict_types=1);

namespace SiteBundle\Parser\Resolver;

use SiteBundle\Dto\User\UpdateUserRequest;
use SiteBundle\Parser\UpdateUserRequestParser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class UpdateUserRequestResolver implements ValueResolverInterface
{
    public function __construct(
        private readonly UpdateUserRequestParser $parser,
    ) {
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (UpdateUserRequest::class !== $argument->getType()) {
            return [];
        }

        yield $this->parser->fromRequest($request);
    }
}
