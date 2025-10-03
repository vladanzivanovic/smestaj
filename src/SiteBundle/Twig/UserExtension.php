<?php

namespace SiteBundle\Twig;

use SiteBundle\Entity\User;

class UserExtension extends \Twig_Extension
{
    /**
     * @return array
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('hasanyrole', [$this, 'hasAnyRoleFilter'])
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
