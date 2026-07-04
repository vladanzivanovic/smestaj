<?php

declare(strict_types=1);

namespace SiteBundle\Parser;

use SiteBundle\Dto\User\UpdateUserRequest;
use Symfony\Component\HttpFoundation\Request;

final class UpdateUserRequestParser
{
    public function fromRequest(Request $request): UpdateUserRequest
    {
        $dto = new UpdateUserRequest();

        $email = $request->request->get('email');
        $password = $request->request->get('password');
        $firstname = $request->request->get('firstname');
        $lastname = $request->request->get('lastname');
        $repassword = $request->request->get('repassword');

        $dto->email = null === $email ? null : (string) $email;
        $dto->password = null === $password ? null : (string) $password;
        $dto->firstname = null === $firstname ? null : (string) $firstname;
        $dto->lastname = null === $lastname ? null : (string) $lastname;
        $dto->repassword = null === $repassword ? null : (string) $repassword;

        return $dto;
    }

    /**
     * Returns only the keys the client actually supplied (non-null), so the
     * downstream array-input handler receives a partial payload that matches
     * PATCH-semantics on a PUT route.
     *
     * @return array<string, string>
     */
    public function toArray(UpdateUserRequest $dto): array
    {
        $data = [];

        if (null !== $dto->email) {
            $data['email'] = $dto->email;
        }

        if (null !== $dto->password) {
            $data['password'] = $dto->password;
        }

        if (null !== $dto->firstname) {
            $data['firstname'] = $dto->firstname;
        }

        if (null !== $dto->lastname) {
            $data['lastname'] = $dto->lastname;
        }

        if (null !== $dto->repassword) {
            $data['repassword'] = $dto->repassword;
        }

        return $data;
    }
}
