<?php

namespace SiteBundle\Services;

use Doctrine\Common\Persistence\ObjectManager;
use SiteBundle\Constants\EmailConstants;
use SiteBundle\Constants\MessageConstants;
use SiteBundle\Entity\EntityStatusInterface;
use SiteBundle\Entity\User;
use SiteBundle\Exceptions\ApplicationException;
use SiteBundle\Handler\UserHandler;
use SiteBundle\Helper\Email;
use SiteBundle\Helper\ValidatorHelper;
use SiteBundle\Repository\UserRepository;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserService extends ServiceContainer
{
    protected $userHandler;
    protected $email;
    private $parameterBag;
    private $validator;
    private $userRepository;

    /**
     * UserService constructor.
     *
     * @param UserHandler           $userHandler
     * @param Email                 $email
     * @param ParameterBagInterface $parameterBag
     * @param ValidatorHelper       $validator
     * @param ObjectManager         $objectManager
     * @param TokenStorageInterface $tokenStorage
     * @param UserRepository        $userRepository
     */
    public function __construct(
        UserHandler $userHandler,
        Email $email,
        ParameterBagInterface $parameterBag,
        ValidatorHelper $validator,
        ObjectManager $objectManager,
        TokenStorageInterface $tokenStorage,
        UserRepository $userRepository
    ) {
        $this->userHandler = $userHandler;
        $this->email = $email;
        $this->parameterBag = $parameterBag;
        $this->validator = $validator;

        parent::__construct($objectManager, $tokenStorage);
        $this->userRepository = $userRepository;
    }

    /**
     * Activate User registration
     * @param User $user
     * @return bool|null|string
     */
    public function activateRegistration($user)
    {

        $userResponse = 'already_register';

        if(0 === $user->getStatus()) {
            $user->setStatus(EntityStatusInterface::STATUS_ACTIVE);

            $this->em->beginTransaction();

            try {
                $this->userHandler->setUser($user, $user->getId());
                $userResponse = 'success_activated';
                $this->em->commit();
            } catch (\Exception $exception) {
                $this->em->rollback();
                $userResponse = null;
            }

        }
        return $userResponse;
    }

//    public function resetPassword($email)
//    {
//        if(!($userObj = $this->em->getRepository('SiteBundle:User')->findOneBy(['email' => $email])))
//            throw new ApplicationException(MessageConstants::NOT_FOUND);
//
//        $data['password'] = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0 , 10 );
//
//        /** @var User $userObj */
//        $userObj = $this->userHandler->setUser($data, $userObj->getId());
//        $data['email'] = $email;
//        $data['id'] = $userObj->getId();
//
//        $emailData = array_merge($this->prepareDataForEmail($data, 'resetPassword'), $data);
//        $this->email->setAndSendEmail($emailData);
//
//        $this->em->commit();
//
//        $userResponse['status'] = true;
//
//        return $userResponse;
//    }

    private function checkUserPassword(array &$data)
    {
        if($data['password'] !== $data['reNewPassword'])
            throw new \Exception(MessageConstants::PASSWORD_NOT_EQUAL);
    }

    private function prepareDataForEmail(array $data, $method = null)
    {
        $emailData = [];
        $siteInfo = $this->parameterBag->get('site_info');

        $emailData['fromEmail'] = $siteInfo['site_email'];
        $emailData['toEmail'] = $data['email'];
        $emailData['userId'] = $data['id'];

        switch ($method){
            case 'resetPassword':
                $emailData['subject'] = 'Reset lozinke - '. $siteInfo['site_name'];
                $emailData['template'] = 'user_reset_password';
                $emailData['script'] = EmailConstants::USER_RESET_PASSWORD;
                break;
            default:
                $emailData['subject'] = 'Registracija - '. $siteInfo['site_name'];
                $emailData['template'] = 'user_registration';
                $emailData['script'] = EmailConstants::USER_REGISTRATION_SCRIPT;
                break;
        }

        return $emailData;
    }
}
