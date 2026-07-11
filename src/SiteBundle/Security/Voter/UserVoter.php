<?php

declare(strict_types=1);

namespace SiteBundle\Security\Voter;

use SiteBundle\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class UserVoter extends Voter
{
    public const string EDIT = 'USER_EDIT';

    public function __construct(private readonly Security $security)
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return self::EDIT === $attribute && $subject instanceof User;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $currentUser = $token->getUser();

        if (false === $currentUser instanceof User) {
            return false;
        }

        if (true === $this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        return $subject->getId() === $currentUser->getId();
    }
}
