<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Exception\ApiException;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;

#[AsEventListener(event: 'kernel.exception')]
readonly class ApiExceptionListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof UnexpectedValueException) {
            $this->handleSerializationException($event, $exception);
            return;
        }

        if (!$exception instanceof ApiException) {
            return;
        }

        $response = new JsonResponse([
            'code' => $exception->getCode(),
            'message' => $exception->getMessage(),
            'context' => $exception->getErrorContext()
        ], $exception->getStatusCode());

        $event->setResponse($response);
    }

    private function handleSerializationException(ExceptionEvent $event, UnexpectedValueException $exception): void
    {
        $message = $exception->getMessage();

        if (str_contains($message, 'Item not found for')) {
            $apiException = new ApiException(Response::HTTP_NOT_FOUND, 'Object not found');
            $event->setThrowable($apiException);
        }
    }
}
