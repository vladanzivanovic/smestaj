<?php

declare(strict_types=1);

namespace SiteBundle\EventListeners;

use SiteBundle\Constants\MessageConstants;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;

#[AsEventListener(event: 'kernel.exception', priority: 128)]
final class PayloadMappingFailureListener
{
    private const ALLOWED_STATUS_CODES = [
        Response::HTTP_BAD_REQUEST,
        Response::HTTP_UNSUPPORTED_MEDIA_TYPE,
        Response::HTTP_UNPROCESSABLE_ENTITY,
    ];

    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if (false === $exception instanceof HttpException) {
            return;
        }

        if (false === in_array($exception->getStatusCode(), self::ALLOWED_STATUS_CODES, true)) {
            return;
        }

        $route = (string) $event->getRequest()->attributes->get('_route', '');

        $previous = $exception->getPrevious();
        $violations = $previous instanceof ValidationFailedException
            ? $previous->getViolations()
            : null;

        $response = match ($route) {
            'admin.api.info_pages.update',
            'admin.api.info_pages.create' => $this->formatOkFalseViolations($violations),

            'admin.api.info_pages.toggle_publish',
            'admin.api.info_pages.remove',
            'admin.disable_user_api',
            'admin.remove_product_api' => $this->formatOkFalseError($exception),

            'admin.add_user_api',
            'admin.edit_user_api',
            'admin.add_product_api',
            'admin.edit_product_api' => $this->formatErrorMsg($exception),

            'site_registration_post',
            'site_user_update',
            'site_ad_reservation' => $this->formatEmptyRequest(),

            'site_api_ads_view',
            'site_api_ads_view_by_all_categories',
            'site_ads_view',
            'site_ads_view_by_all_categories' => $this->formatMsgThrowable($exception),

            'site_send_contact_us' => $this->formatContactShape($violations),

            'site_ads_save',
            'site_ads_image_resize_on_fly' => $this->formatBrackets(),

            'site_ads_update' => $this->formatNull(),

            default => null,
        };

        if (null === $response) {
            return;
        }

        $event->setResponse($response);
    }

    private function formatOkFalseViolations(?ConstraintViolationListInterface $violations): JsonResponse
    {
        $items = [];

        if (null !== $violations) {
            foreach ($violations as $violation) {
                $items[] = [
                    'field' => $violation->getPropertyPath(),
                    'message' => (string) $violation->getMessage(),
                ];
            }
        }

        return new JsonResponse([
            'ok' => false,
            'violations' => $items,
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    private function formatOkFalseError(HttpException $exception): JsonResponse
    {
        return new JsonResponse([
            'ok' => false,
            'error' => $exception->getMessage(),
        ], Response::HTTP_BAD_REQUEST);
    }

    private function formatErrorMsg(HttpException $exception): JsonResponse
    {
        return new JsonResponse([
            'error' => $exception->getMessage(),
        ], Response::HTTP_BAD_REQUEST);
    }

    private function formatEmptyRequest(): JsonResponse
    {
        return new JsonResponse([
            'msg' => MessageConstants::EMPTY_REQUEST,
        ], Response::HTTP_BAD_REQUEST);
    }

    private function formatMsgThrowable(HttpException $exception): JsonResponse
    {
        return new JsonResponse([
            'msg' => $exception->getMessage(),
        ], Response::HTTP_BAD_REQUEST);
    }

    private function formatContactShape(?ConstraintViolationListInterface $violations): JsonResponse
    {
        if (null === $violations) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Invalid request payload.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $errors = [];
        $firstMessage = '';

        foreach ($violations as $violation) {
            $field = $violation->getPropertyPath();
            $message = (string) $violation->getMessage();
            $errors[$field] = $message;

            if ('' === $firstMessage) {
                $firstMessage = $message;
            }
        }

        return new JsonResponse([
            'status' => 'error',
            'message' => $firstMessage,
            'errors' => $errors,
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    private function formatBrackets(): JsonResponse
    {
        return new JsonResponse([], Response::HTTP_BAD_REQUEST);
    }

    private function formatNull(): JsonResponse
    {
        return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
    }
}
