<?php

declare(strict_types=1);

namespace Matav5\ViesSdk\Exception;

use Psr\Http\Message\ResponseInterface;

class ApiException extends ViesSdkException
{
    public function __construct(
        string $message,
        private readonly ResponseInterface $response,
        int $code = 0,
        private readonly array $errorWrappers = [],
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    public function getStatusCode(): int
    {
        return $this->response->getStatusCode();
    }

    public function getErrorCodes(): array
    {
        return array_map(
            static fn(array $wrapper): string => $wrapper['error'] ?? '',
            $this->errorWrappers,
        );
    }
}
