<?php

declare(strict_types=1);

namespace Matav5\ViesSdk\Exception;

class ApiException extends ViesSdkException
{
    public function __construct(
        string $message,
        private readonly int $statusCode,
        private readonly array $errorWrappers = [],
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $statusCode, $previous);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getErrorCodes(): array
    {
        return array_map(
            static fn(array $wrapper): string => $wrapper['error'] ?? '',
            $this->errorWrappers,
        );
    }
}
