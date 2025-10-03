<?php

declare(strict_types=1);

namespace AdminBundle\Parser;

use SiteBundle\Entity\Ads;
use SiteBundle\Entity\EntityInterface;
use SiteBundle\Entity\EntityStatusInterface;
use SiteBundle\Entity\Role;
use SiteBundle\Entity\User;
use SiteBundle\Entity\Usertorole;
use SiteBundle\Helper\TextHelper;
use SiteBundle\Parser\AdsEditParser;
use SiteBundle\Parser\UserToRoleParser;
use SiteBundle\Repository\UserRepository;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class ProductEditRequestParser implements RequestParserInterface
{
    use ParserTrait;

    private ParameterBagInterface $parameterBag;

    /**
     * @var array<int, array<string, string>>
     */
    private array $languages;

    private TextHelper $textHelper;

    private AdsEditParser $adsEditParser;

    private TokenStorageInterface $tokenStorage;

    private UserPasswordEncoderInterface $passwordEncoder;

    private UserToRoleParser $userToRoleParser;

    private UserRepository $userRepository;

    private ProductPaymentRequestParser $paymentRequestParser;

    /**
     * @param array<int, array<string, string>> $languages
     */
    public function __construct(
        ParameterBagInterface $parameterBag,
        TextHelper $textHelper,
        AdsEditParser $adsEditParser,
        TokenStorageInterface $tokenStorage,
        UserPasswordEncoderInterface $passwordEncoder,
        UserToRoleParser $userToRoleParser,
        UserRepository $userRepository,
        ProductPaymentRequestParser $paymentRequestParser,
        array $languages
    ) {
        $this->parameterBag = $parameterBag;
        $this->languages = $languages;
        $this->textHelper = $textHelper;
        $this->adsEditParser = $adsEditParser;
        $this->tokenStorage = $tokenStorage;
        $this->passwordEncoder = $passwordEncoder;
        $this->userToRoleParser = $userToRoleParser;
        $this->userRepository = $userRepository;
        $this->paymentRequestParser = $paymentRequestParser;
    }

    /**
     * @param ParameterBag $bag
     * @param EntityInterface|null $entity
     *
     * @return EntityInterface
     * @throws \Doctrine\ORM\ORMException
     * @throws \SiteBundle\Exceptions\ApplicationException
     * @throws \Exception
     */
    public function parse(ParameterBag $bag, EntityInterface $entity = null): EntityInterface
    {
        $user = $this->tokenStorage->getToken()->getUser();

        $contact = $bag->get('contact');

        $owner = $this->userRepository->find($bag->getInt('owner'));

        $entity = $this->adsEditParser->parse($bag, $user, $owner, $entity);

        $contactUser = $entity->getContact();

        $entity->setShortDescription($this->textHelper->clearText($bag->get('short_description_rs')));

        if (null === $entity->getId() && null === $owner && isset($contact['password'])) {
            $this->setOwnerFromContact($contactUser, $entity, $contact['password']);
        }

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
            $owner->setRoles($this->userToRoleParser->parse(new ParameterBag(['user' => $owner, 'user_role' => Role::ROLE_ADVANCED_USER])));
            $owner->setPassword($this->passwordEncoder->encodePassword($owner, $password));
            $owner->setStatus(EntityStatusInterface::STATUS_ACTIVE);
        }

        $ads->setOwner($owner);
    }
}
