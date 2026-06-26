<?php

declare(strict_types=1);

namespace SiteBundle\Parser\Resolver;

use SiteBundle\Dto\User\RegisterUserRequest;
use SiteBundle\Parser\RegisterUserRequestParser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class RegisterUserRequestResolver implements ValueResolverInterface
{
    public function __construct(
        private readonly RegisterUserRequestParser $parser,
    ) {
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (RegisterUserRequest::class !== $argument->getType()) {
            return [];
        }

        yield $this->parser->fromRequest($request);
    }
}
