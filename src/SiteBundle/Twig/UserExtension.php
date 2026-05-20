<?php

namespace SiteBundle\Twig;

use SiteBundle\Entity\User;

class UserExtension extends \Twig\Extension\AbstractExtension
{
    /**
     * @return array
     */
    public function getFilters(): array
    {
        return [
            new \Twig\TwigFilter('hasanyrole', [$this, 'hasAnyRoleFilter'])
        ];
    }

    /**
     * @param User $user
     * @return bool
     */
    public function hasAnyRoleFilter($user)
    {
        if($user instanceof User) {
            return !empty($user->getRoles());
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'user_extension';
    }
}
