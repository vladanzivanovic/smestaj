<?php

namespace SiteBundle\Helper;

use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use SiteBundle\Constants\MessageConstants;
use SiteBundle\Entity\Emails;
use SiteBundle\Services\ServiceContainer;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email as MimeEmail;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Twig\Environment;

final class Email extends ServiceContainer
{
    protected EntityManagerInterface $lem;
    protected MailerInterface $mailer;
    protected Environment $twig;
    private RandomCodeGenerator $codeGenerator;
    private ParameterBagInterface $parameterBag;

    public function __construct(
        ObjectManager $objectManager,
        TokenStorageInterface $tokenStorage,
        EntityManagerInterface $loggerEm,
        MailerInterface $mailer,
        Environment $twig,
        RandomCodeGenerator $codeGenerator,
        ParameterBagInterface $parameterBag
    )
    {
        parent::__construct($objectManager, $tokenStorage);
        $this->lem = $loggerEm;
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->codeGenerator = $codeGenerator;
        $this->parameterBag = $parameterBag;
    }

    /**
     * Prepare data and send email
     * @param array $data
     * @return string
     * @throws \Exception
     */
    public function setAndSendEmail(array $data)
    {
        $data['templateData']['code'] = $this->codeGenerator->random();

        $body = $this->twig->render(
            "@Site/Email/" . $data['template'] . ".html.twig", $data
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
     * @throws \Exception
     */
    private function send($data, $body, $subject = null, ?array $attachments = null)
    {
        try {
            $siteInfo = $this->parameterBag->get('site_info');
            $email = (new MimeEmail())
                ->subject($subject)
                ->from(new Address($siteInfo['site_email'], $siteInfo['site_name']))
                ->replyTo(new Address($data['replyTo'], $data['replyToName'] ?? ''))
                ->to(new Address($data['toEmail'], $data['toEmailName'] ?? ''))
                ->html($body);

            if (!empty($attachments)) {
                foreach ($attachments as $attachment) {
                    $email->attachFromPath($attachment);
                }
            }

            $this->mailer->send($email);
            $data['status'] = Emails::EMAIL_SUCCESS;
            $this->saveEmail($data);
        } catch (TransportExceptionInterface $e) {
            $data['status'] = Emails::EMAIL_FAILED;
            $data['errorMessage'] = $e->getMessage();
            $this->saveEmail($data);
            throw new \RuntimeException(MessageConstants::EMAIL_NOT_SENT, 0, $e);
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
            $this->lem->flush();
            $this->lem->clear();
        }
    }
}
