<?php

declare(strict_types=1);

namespace SiteBundle\Controller\Api;

use JsonException;
use Psr\Log\LoggerInterface;
use SiteBundle\Dto\ContactFormRequest;
use SiteBundle\Services\Contact\ContactMailer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ContactController extends AbstractController
{
    public function __construct(
        private readonly ValidatorInterface $validator,
        private readonly ContactMailer $mailer,
        private readonly TranslatorInterface $translator,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function submit(Request $request): JsonResponse
    {
        if ('json' !== $request->getContentTypeFormat()) {
            return new JsonResponse(
                ['status' => 'error', 'message' => 'Invalid request payload.'],
                Response::HTTP_BAD_REQUEST
            );
        }

        $raw = $request->getContent();
        try {
            $payload = json_decode($raw, true, 512, \JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return new JsonResponse(
                ['status' => 'error', 'message' => 'Invalid request payload.'],
                Response::HTTP_BAD_REQUEST
            );
        }

        if (false === \is_array($payload)) {
            return new JsonResponse(
                ['status' => 'error', 'message' => 'Invalid request payload.'],
                Response::HTTP_BAD_REQUEST
            );
        }

        $dto = new ContactFormRequest();
        $dto->name = \is_string($payload['name'] ?? null) ? trim($payload['name']) : '';
        $dto->email = \is_string($payload['email'] ?? null) ? trim($payload['email']) : '';
        $dto->subject = \is_string($payload['subject'] ?? null) ? trim($payload['subject']) : '';
        $dto->message = \is_string($payload['message'] ?? null) ? trim($payload['message']) : '';
        $dto->website = \is_string($payload['website'] ?? null) ? $payload['website'] : '';

        if ('' !== $dto->website) {
            $this->logger->info('Contact form honeypot triggered.', ['ip' => $request->getClientIp()]);

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }

        $violations = $this->validator->validate($dto);
        if (0 < \count($violations)) {
            $errors = [];
            foreach ($violations as $violation) {
                $field = $violation->getPropertyPath();
                $errors[$field][] = (string) $violation->getMessage();
            }

            return new JsonResponse(
                [
                    'status' => 'error',
                    'message' => $this->translator->trans('Molimo proverite unete podatke.'),
                    'errors' => $errors,
                ],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        try {
            $this->mailer->send($dto);
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('Contact form mail transport failure.', ['exception' => $e]);

            return new JsonResponse(
                [
                    'status' => 'error',
                    'message' => $this->translator->trans('Trenutno ne možemo poslati vašu poruku. Pokušajte ponovo kasnije.'),
                ],
                Response::HTTP_BAD_GATEWAY
            );
        }

        return new JsonResponse([
            'status' => 'ok',
            'message' => $this->translator->trans('Vaša poruka je uspešno poslata.'),
        ]);
    }
}
