<?php

namespace SiteBundle\Helper;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use SiteBundle\Constants\MessageConstants;
use SiteBundle\Entity\Emails;
use SiteBundle\Services\ServiceContainer;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Templating\EngineInterface;

final class Email extends ServiceContainer
{
    protected EntityManagerInterface $lem;
    protected \Swift_Mailer $mailer;
    protected EngineInterface $templateEngine;
    private RandomCodeGenerator $codeGenerator;
    private ParameterBagInterface $parameterBag;

    public function __construct(
        ObjectManager $objectManager,
        TokenStorageInterface $tokenStorage,
        EntityManagerInterface $loggerEm,
        \Swift_Mailer $mailer,
        EngineInterface $templateEngine,
        RandomCodeGenerator $codeGenerator,
        ParameterBagInterface $parameterBag
    )
    {
        parent::__construct($objectManager, $tokenStorage);
        $this->lem = $loggerEm;
        $this->mailer = $mailer;
        $this->templateEngine = $templateEngine;
        $this->codeGenerator = $codeGenerator;
        $this->parameterBag = $parameterBag;
    }

    /**
     * Prepare data and send email
     * @param array $data
     * @return string
     * @throws \Twig_Error
     * @throws \Swift_SwiftException
     * @throws \RuntimeException
     */
    public function setAndSendEmail(array $data)
    {
        $data['templateData']['code'] = $this->codeGenerator->random();

        $body = $this->templateEngine->render(
            "SiteBundle:Email:" . $data['template'] . ".html.twig", $data
        );
        $attachments = $data['attachments'] ?? null;
        $subject = $data['subject'] ?? 'Poruka sa sajta smestaj.me';

        return $this->send($data, $body, $subject, $attachments);
    }

    /**
     * Send email
     *
     * @param $data
     * @param $body
     * @param null $subject
     * @param array $attachments
     * @return string
     * @throws \Swift_SwiftException
     */
    private function send($data, $body, $subject = null, array $attachments = null)
    {
        try {
            $siteInfo = $this->parameterBag->get('site_info');
            $emailInstance = (new \Swift_Message())
                ->setSubject($subject)
                ->setFrom($siteInfo['site_email'], $siteInfo['site_name'])
                ->setReplyTo($data['replyTo'], $data['replyToName'] ?? null)
                ->setTo($data['toEmail'], $data['toEmailName'] ?? null)
                ->setBody($body, 'text/html');

            if (!empty($attachments)) {
                foreach ($attachments as $attachment) {
                    $emailInstance->attach(( new \Swift_Attachment())->setFile($attachment));
                }
            }

            $response = $this->mailer->send($emailInstance);
            $data['status'] = Emails::EMAIL_SUCCESS;
            $this->saveEmail($data);
        } catch (\Swift_TransportException $swift_TransportException){
            $data['status'] = Emails::EMAIL_FAILED;
            $data['errorMessage'] = $swift_TransportException->getMessage();
            $this->saveEmail($data);
            throw new \Swift_SwiftException(MessageConstants::EMAIL_NOT_SENT);
        }
        return true;
    }

    private function saveEmail(array $data)
    {
        if(!empty($data)){
            $email = new Emails();

            $email->setFromemail(isset($data['replyTo']) ? $data['replyTo'] : $data['fromEmail']);
            $email->setToemail($data['toEmail']);
            $email->setRawdata(json_encode($data));
            $email->setStatus($data['status']);
            $email->setErrormessage(isset($data['errorMessage']) ? $data['errorMessage'] : null);
            $email->setScript($data['script']);
            $email->setSyscreatedutc(new \DateTime());
            $email->setCode($data['templateData']['code']);

            $this->lem->persist($email);
            $this->lem->flush($email);
            $this->lem->clear();
        }
    }
}