<?php

declare(strict_types=1);

namespace SiteBundle\Parser;

use JsonException;
use SiteBundle\Dto\Contact\ContactRequestError;
use SiteBundle\Dto\ContactFormRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ContactFormRequestParser
{
    public function __construct(
        private readonly ValidatorInterface $validator,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function fromRequest(Request $request): ContactFormRequest
    {
        $dto = new ContactFormRequest();

        if ('json' !== $request->getContentTypeFormat()) {
            $dto->error = new ContactRequestError(
                Response::HTTP_BAD_REQUEST,
                'Invalid request payload.',
            );

            return $dto;
        }

        try {
            $payload = json_decode($request->getContent(), true, 512, \JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            $dto->error = new ContactRequestError(
                Response::HTTP_BAD_REQUEST,
                'Invalid request payload.',
            );

            return $dto;
        }

        if (false === \is_array($payload)) {
            $dto->error = new ContactRequestError(
                Response::HTTP_BAD_REQUEST,
                'Invalid request payload.',
            );

            return $dto;
        }

        $dto->name = \is_string($payload['name'] ?? null) ? trim($payload['name']) : '';
        $dto->email = \is_string($payload['email'] ?? null) ? trim($payload['email']) : '';
        $dto->subject = \is_string($payload['subject'] ?? null) ? trim($payload['subject']) : '';
        $dto->message = \is_string($payload['message'] ?? null) ? trim($payload['message']) : '';
        $dto->website = \is_string($payload['website'] ?? null) ? $payload['website'] : '';

        $violations = $this->validator->validate($dto);

        if (0 < \count($violations)) {
            $errorsMap = [];

            foreach ($violations as $violation) {
                $errorsMap[$violation->getPropertyPath()][] = (string) $violation->getMessage();
            }

            $dto->error = new ContactRequestError(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                $this->translator->trans('Molimo proverite unete podatke.'),
                $errorsMap,
            );
        }

        return $dto;
    }
}
