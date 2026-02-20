<?php

declare(strict_types=1);

namespace Matav5\ViesSdk;

class Config
{
    public const BASE_URL = 'https://ec.europa.eu/taxation_customs/vies/rest-api';

    public function __construct(
        private readonly string $baseUrl = self::BASE_URL,
        private readonly int $timeout = 30,
    ) {
    }

    public function getBaseUrl(): string
    {
        return rtrim($this->baseUrl, '/');
    }

    public function getTimeout(): int
    {
        return $this->timeout;
    }
}
