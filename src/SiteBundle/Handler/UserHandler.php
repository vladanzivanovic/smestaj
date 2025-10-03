<?php

namespace SiteBundle\Handler;


use Doctrine\Common\Persistence\ObjectManager;
use Psr\Log\LoggerInterface;
use SiteBundle\Entity\EntityStatusInterface;
use SiteBundle\Entity\User;
use SiteBundle\Entity\UserToSocialNetwork;
use SiteBundle\Helper\ValidatorHelper;
use SiteBundle\Repository\AdsRepository;
use SiteBundle\Repository\UserRepository;
use SiteBundle\Services\ServiceContainer;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserHandler extends ServiceContainer
{
    protected UserPasswordEncoderInterface $passwordEncoder;
    protected RoleHandler $roleHandler;
    private UserRepository $userRepository;
    private ValidatorHelper $validator;
    private AdsRepository $adsRepository;
    private LoggerInterface $logger;
    private TranslatorInterface $translator;

    public function __construct(
        UserPasswordEncoderInterface $passwordEncoder,
        RoleHandler $roleHandler,
        UserRepository $userRepository,
        ValidatorHelper $validator,
        ObjectManager $objectManager,
        TokenStorageInterface $tokenStorage,
        AdsRepository $adsRepository,
        LoggerInterface $logger,
        TranslatorInterface $translator
    ) {
        $this->passwordEncoder = $passwordEncoder;
        $this->roleHandler = $roleHandler;
        $this->userRepository = $userRepository;
        $this->validator = $validator;

        parent::__construct($objectManager, $tokenStorage);
        $this->adsRepository = $adsRepository;
        $this->logger = $logger;
        $this->translator = $translator;
    }

    public function insertUser(array $data)
    {
        try {
            $data['status'] = EntityStatusInterface::STATUS_PENDING;
            $data['role'] = 'ROLE_ADVANCED_USER';

            $user = $this->createUserEntity($data);

            $errors = $this->validator->validate($user, null, ['Registration']);

            if ($errors->count() > 0) {
                return $this->validator->parseErrors($errors);
            }

            $user->setPassword($this->passwordEncoder->encodePassword($user, $data['password']));
            $user = $this->roleHandler->setUserToRole($user, $data['role']);
            $user = $this->setSocialData($user, $data);

            $this->userRepository->persist($user);
            $this->userRepository->flush();

            return true;
        }catch (\Throwable $throwable) {
            $this->logger->warning(
                sprintf(
                'User registration failed. Registration data: %s',
                    json_encode($data, JSON_THROW_ON_ERROR),
                ),
                ['exception' => $throwable],
            );

            return ['error' => $throwable->getMessage()];
        }
    }

    public function activateRegistration(User $user): string
    {
        try {
            if($user->getStatus() != EntityStatusInterface::STATUS_PENDING) {
                return 'user_activation.already_registered';
            }
            $user->setStatus(EntityStatusInterface::STATUS_ACTIVE);
            $this->userRepository->flush();
            return 'user_activation.success';

        } catch (\Exception $exception) {
            return 'generic_error';
        }
    }

    public function saveUser(User $user): void
    {
        $this->userRepository->flush();
    }

    public function setResetPassword(User $user): bool
    {
        $token = bin2hex(openssl_random_pseudo_bytes(10));

        $user->setToken($token);
        $user->setTokenValid(new \DateTime());
        $user->setIsResetPasswordRequest(true);

        $this->adsRepository->flush();

        return true;
    }

    /**
     * @param User  $user
     * @param array $data
     *
     * @return array|bool
     */
    public function doResetPassword(User $user, array $data)
    {
        $user->setPassword($data['password']);
        $user->setRepassword($data['rePassword']);

        $errors = $this->validator->validate($user, null, ['ResetPassword']);

        if ($errors->count() > 0) {
            return $this->validator->parseErrors($errors);
        }

        $user->setPassword($this->passwordEncoder->encodePassword($user, $data['password']));
        $user->setToken(null);
        $user->setTokenValid(null);

        $this->userRepository->flush();

        return true;
    }

    /**
     * Insert or update user
     * @param array|User $user
     * @param null $id
     * @return User
     * @throws \Doctrine\ORM\Mapping\MappingException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     */
    public function setUser($user, $id = null)
    {
        $userObj = null;

        if (null === $id)
            $userObj = $this->insertUser($user);
        elseif((int)$id > 0)
            $userObj = $this->updateUser($user, $id);

        return $userObj;
    }

    private function updateUser($user, $id)
    {
        $userObj = $user;

        if(!($user instanceof User) && is_array($userObj)) {

            $user['id'] = $id;
            /** @var User $userObj */
            $userObj = $this->arrayToEntity($user, 'SiteBundle:User');

            if(isset($user['password']))
                $userObj->setPassword($this->passwordEncoder->encodePassword($userObj, $user['password']));

            if(isset($user['role'])) {
                $userObj->removeAllRoles();
                $userObj = $this->roleHandler->setUserToRole($userObj, $user['role']);
            }
        }

        $this->updateData($userObj);

        return $userObj;
    }

    private function setSocialData(User $user, array $data) {

        if(isset($data['facebookId'])) {
            $social = new UserToSocialNetwork();
            $social->setUserid($user)
                ->setSocialId($data['facebookId'])
                ->setType(UserToSocialNetwork::FACEBOOK_TYPE)
                ->setImage(isset($data['facebookImage']) ? $data['facebookImage'] : null);

            $user->addSocialid($social);
            $user->setStatus(EntityStatusInterface::STATUS_ACTIVE);
        }

        return $user;
    }

    private function createUserEntity(array $data): User
    {
        $exstingUser = $this->userRepository->findOneBy(['email' => $data['email']]);

        if (null !== $exstingUser) {
            throw new BadRequestHttpException($this->translator->trans('user.email_exists'));
        }

        /** @var User $user */
        $user = $this->arrayToEntity($data, User::class);

        return $user;
}
}
