<?php

declare(strict_types=1);

namespace SiteBundle\Parser;

use SiteBundle\Dto\Ads\AdsSaveRequest;
use SiteBundle\Entity\Ads;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Orchestrates the DTO → Entity translation for BOTH POST /api/product
 * (insert) and PUT /product/{id} (update). Delegates the field-by-field
 * hydration to AdsEditParser; the nullable $ads discriminates the two paths
 * (null ⇒ fresh Ads with STATUS_PENDING; non-null ⇒ mutate in place).
 */
final class AdsSaveRequestParser
{
    public function __construct(
        private readonly AdsEditParser $adsEditParser,
        private readonly TokenStorageInterface $tokenStorage,
    ) {
    }

    public function parse(AdsSaveRequest $dto, ?Ads $ads = null): Ads
    {
        $user = $this->tokenStorage->getToken()?->getUser();

        if (false === $user instanceof UserInterface) {
            throw new \RuntimeException('AdsSaveRequestParser requires an authenticated user in the token storage.');
        }

        return $this->adsEditParser->parse($dto, $user, $user, $ads);
    }
}
