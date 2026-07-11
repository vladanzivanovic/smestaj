<?php

declare(strict_types=1);

namespace SiteBundle\Parser;

use SiteBundle\Dto\User\UserSaveRequest;
use SiteBundle\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Consolidated parser for BOTH POST /api/add-new-user (register path)
 * and PUT /user/{id} (update path). Merges the intent of the two
 * retired paired parsers.
 *
 * The `?User` argument on `parse` discriminates:
 *   - null       ⇒ register (fresh User + always-hash-password path)
 *   - non-null   ⇒ update   (patch present fields + rehash only if
 *                             password was submitted)
 *
 * Two typed `toArray*` methods preserve byte-for-byte compatibility
 * with the two divergent downstream handlers:
 *   - toRegistrationArray ⇒ UserHandler::insertUser(array) — always
 *                            5 shared keys + optional facebook.
 */
final class UserSaveRequestParser
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function parse(UserSaveRequest $dto, ?User $user = null): User
    {
        if (null === $user) {
            $user = new User();
            $user->setEmail((string) $dto->email);
            $user->setFirstname((string) $dto->firstname);
            $user->setLastname((string) $dto->lastname);
            $user->setRepassword((string) $dto->repassword);
            $user->setPassword($this->passwordHasher->hashPassword($user, (string) $dto->password));

            return $user;
        }

        if (null !== $dto->email) {
            $user->setEmail($dto->email);
        }

        if (null !== $dto->firstname) {
            $user->setFirstname($dto->firstname);
        }

        if (null !== $dto->lastname) {
            $user->setLastname($dto->lastname);
        }

        if (null !== $dto->repassword) {
            $user->setRepassword($dto->repassword);
        }

        if (null !== $dto->password) {
            $user->setPassword($this->passwordHasher->hashPassword($user, $dto->password));
        }

        return $user;
    }

    /**
     * @return array<string, string>
     */
    public function toRegistrationArray(UserSaveRequest $dto): array
    {
        $data = [
            'email' => (string) $dto->email,
            'password' => (string) $dto->password,
            'firstname' => (string) $dto->firstname,
            'lastname' => (string) $dto->lastname,
            'repassword' => (string) $dto->repassword,
        ];

        if (null !== $dto->facebookId) {
            $data['facebookId'] = $dto->facebookId;
        }

        if (null !== $dto->facebookImage) {
            $data['facebookImage'] = $dto->facebookImage;
        }

        return $data;
    }

    /**
     * @return array<string, string>
     */
    public function toUpdateArray(UserSaveRequest $dto): array
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
