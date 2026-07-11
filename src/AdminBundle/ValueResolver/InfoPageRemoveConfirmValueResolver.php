<?php

declare(strict_types=1);

namespace AdminBundle\ValueResolver;

use AdminBundle\Dto\InfoPage\InfoPageRemoveDto;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class InfoPageRemoveConfirmValueResolver implements ValueResolverInterface
{
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (InfoPageRemoveDto::class !== $argument->getType()) {
            return [];
        }

        $dto = new InfoPageRemoveDto();
        $dto->confirm = (string) $request->query->get('confirm', '');
        $dto->id = (int) $request->attributes->get('id', 0);

        yield $dto;
    }
}
