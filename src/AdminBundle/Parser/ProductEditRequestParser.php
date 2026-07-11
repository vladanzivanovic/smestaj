<?php

declare(strict_types=1);

namespace AdminBundle\Parser;

use AdminBundle\Dto\Product\ProductSaveRequest;
use SiteBundle\Dto\User\UserRoleAssignmentDto;
use SiteBundle\Entity\Ads;
use SiteBundle\Entity\EntityInterface;
use SiteBundle\Entity\EntityStatusInterface;
use SiteBundle\Entity\Role;
use SiteBundle\Entity\User;
use SiteBundle\Helper\TextHelper;
use SiteBundle\Parser\AdsEditParser;
use SiteBundle\Parser\UserToRoleParser;
use SiteBundle\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class ProductEditRequestParser implements RequestParserInterface
{
    /**
     * @param array<int, array<string, string>> $languages
     */
    public function __construct(
        private readonly TextHelper $textHelper,
        private readonly AdsEditParser $adsEditParser,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly UserPasswordHasherInterface $passwordEncoder,
        private readonly UserToRoleParser $userToRoleParser,
        private readonly UserRepository $userRepository,
        private readonly ProductPaymentRequestParser $paymentRequestParser,
        private readonly array $languages,
    ) {
    }

    /**
     * @param ProductSaveRequest $dto
     */
    public function parse(object $dto, ?EntityInterface $entity = null): Ads
    {
        assert($dto instanceof ProductSaveRequest);

        $user = $this->tokenStorage->getToken()->getUser();

        $owner = 0 < $dto->owner ? $this->userRepository->find($dto->owner) : null;

        $entity = $this->adsEditParser->parse($dto, $user, $owner, $entity);

        $contactUser = $entity->getContact();

        $entity->setShortDescription($this->textHelper->clearText($dto->shortDescription));

        if (null === $entity->getId() && null === $owner && '' !== $dto->contact->password) {
            $this->setOwnerFromContact($contactUser, $entity, $dto->contact->password);
        }

        $this->paymentRequestParser->parse($dto, $entity);

        return $entity;
    }

    public function create(): Ads
    {
        return new Ads();
    }

    private function setOwnerFromContact(User $contact, Ads $ads, string $password): void
    {
        $owner = $this->userRepository->findOneBy(['email' => $contact->getContactemail()]);

        if (null === $owner) {
            $owner = clone $contact;

            $owner->setContactemail(null);
            $owner->setEmail($contact->getContactemail());
            $owner->setRoles($this->userToRoleParser->parse(new UserRoleAssignmentDto($owner, Role::ROLE_ADVANCED_USER)));
            $owner->setPassword($this->passwordEncoder->hashPassword($owner, $password));
            $owner->setStatus(EntityStatusInterface::STATUS_ACTIVE);
        }

        $ads->setOwner($owner);
    }
}
