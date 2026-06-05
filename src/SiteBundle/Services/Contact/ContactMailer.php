<?php

declare(strict_types=1);

namespace SiteBundle\Services\Contact;

use RuntimeException;
use SiteBundle\Dto\ContactFormRequest;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Twig\Environment;

final class ContactMailer
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly Environment $twig,
        private readonly ParameterBagInterface $params,
    ) {
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function send(ContactFormRequest $dto): void
    {
        $recipient = (string) $this->params->get('contact_recipient_email');
        if ('' === $recipient) {
            throw new RuntimeException('contact_recipient_email parameter is not configured.');
        }

        // Prefer site_info (used elsewhere by Helper/Email); fall back to email_address scalar parameter.
        $fromAddress = null;
        if ($this->params->has('site_info')) {
            $siteInfo = $this->params->get('site_info');
            if (\is_array($siteInfo) && !empty($siteInfo['site_email'])) {
                $fromAddress = new Address(
                    (string) $siteInfo['site_email'],
                    (string) ($siteInfo['site_name'] ?? '')
                );
            }
        }
        if (null === $fromAddress) {
            $fallback = (string) $this->params->get('email_address');
            $fromAddress = new Address($fallback);
        }

        $html = $this->twig->render('@Site/Email/contact_form.html.twig', ['data' => $dto]);

        $email = (new Email())
            ->from($fromAddress)
            ->to(new Address($recipient))
            ->replyTo(new Address($dto->email, $dto->name))
            ->subject('[Kontakt forma] ' . $dto->subject)
            ->html($html);

        $this->mailer->send($email);
    }
}
