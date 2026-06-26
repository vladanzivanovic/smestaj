<?php

declare(strict_types=1);

namespace SiteBundle\Parser;

use SiteBundle\Dto\User\SetNewPasswordRequest;

final class SetNewPasswordRequestParser
{
    /**
     * @return array{password: string, rePassword: string}
     */
    public function toArray(SetNewPasswordRequest $dto): array
    {
        return [
            'password' => $dto->password,
            'rePassword' => $dto->rePassword,
        ];
    }
}
