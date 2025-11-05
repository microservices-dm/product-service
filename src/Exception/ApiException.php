<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

final class ApiException extends HttpException
{
    private array $errorContext;

    public function __construct(int $statusCode, string $message = '', array $errorContext = [])
    {
        parent::__construct($statusCode, $message, null, [], $statusCode);
        $this->errorContext = $errorContext;
    }

    public function getErrorContext(): array
    {
        return $this->errorContext;
    }
}
