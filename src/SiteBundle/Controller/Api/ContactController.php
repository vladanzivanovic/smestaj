<?php

declare(strict_types=1);

namespace SiteBundle\Controller\Api;

use Psr\Log\LoggerInterface;
use SiteBundle\Dto\ContactFormRequest;
use SiteBundle\Parser\ContactFormRequestParser;
use SiteBundle\Services\Contact\ContactMailer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ContactController extends AbstractController
{
    public function __construct(
        private readonly ContactMailer $mailer,
        private readonly ContactFormRequestParser $contactFormRequestParser,
        private readonly TranslatorInterface $translator,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function submit(
        #[MapRequestPayload] ContactFormRequest $dto,
    ): JsonResponse {
        if ('' !== $dto->website) {
            $this->logger->info('Contact form honeypot triggered.');

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }

        $submission = $this->contactFormRequestParser->parse($dto);

        try {
            $this->mailer->send($submission);
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('Contact form mail transport failure.', ['exception' => $e]);

            return new JsonResponse([
                'status' => 'error',
                'message' => $this->translator->trans('Trenutno ne možemo poslati vašu poruku. Pokušajte ponovo kasnije.'),
            ], Response::HTTP_BAD_GATEWAY);
        }

        return new JsonResponse([
            'status' => 'ok',
            'message' => $this->translator->trans('Vaša poruka je uspešno poslata.'),
        ]);
    }
}
