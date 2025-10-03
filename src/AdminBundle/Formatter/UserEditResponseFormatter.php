<?php

declare(strict_types=1);

namespace AdminBundle\Formatter;


use SiteBundle\Entity\User;

final class UserEditResponseFormatter
{
    public function __construct(
    ) {
    }

    /**
     * @return array
     */
    public function formatResponse(User $user): array
    {
        /** @var Address $address */
        $address = $user->getUserAddress();

        return [
            'first_name' => $user->getFirstName(),
            'last_name' => $user->getLastName(),
            'email' => $user->getEmail(),
            'role' => $user->getRoles()[0],
            'address' => null !== $address ? $address->getAddress() : '',
            'city' => null !== $address ? $address->getCity() : '',
            'country' => null !== $address ? $address->getCountry() : '',
            'zip_code' => null !== $address ? $address->getZipCode() : '',
            'phone' => null !== $address ? $address->getPhone() : '',
            'note' => $user->getNote(),
            'not_take' => $user->getNotTake(),
        ];
    }
}
