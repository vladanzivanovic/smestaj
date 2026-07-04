<?php

declare(strict_types=1);

namespace SiteBundle\Parser;

use SiteBundle\Dto\User\RegisterUserRequest;
use Symfony\Component\HttpFoundation\Request;

final class RegisterUserRequestParser
{
    public function fromRequest(Request $request): ?RegisterUserRequest
    {
        if (0 === $request->request->count()) {
            return null;
        }

        $dto = new RegisterUserRequest();

        $dto->email = (string) $request->request->get('email', '');
        $dto->password = (string) $request->request->get('password', '');
        $dto->firstname = (string) $request->request->get('firstname', '');
        $dto->lastname = (string) $request->request->get('lastname', '');
        $dto->repassword = (string) $request->request->get('repassword', '');

        $facebookId = $request->request->get('facebookId');
        $facebookImage = $request->request->get('facebookImage');

        $dto->facebookId = null === $facebookId ? null : (string) $facebookId;
        $dto->facebookImage = null === $facebookImage ? null : (string) $facebookImage;

        return $dto;
    }

    /**
     * @return array<string, string|null>
     */
    public function toArray(RegisterUserRequest $dto): array
    {
        $data = [
            'email' => $dto->email,
            'password' => $dto->password,
            'firstname' => $dto->firstname,
            'lastname' => $dto->lastname,
            'repassword' => $dto->repassword,
        ];

        if (null !== $dto->facebookId) {
            $data['facebookId'] = $dto->facebookId;
        }

        if (null !== $dto->facebookImage) {
            $data['facebookImage'] = $dto->facebookImage;
        }

        return $data;
    }
}
