<?php

declare(strict_types=1);

namespace Matav5\ViesSdk\Response;

class CountryStatus
{
    public function __construct(
        private readonly string $countryCode,
        private readonly string $availability,
    ) {
    }

    public function getCountryCode(): string
    {
        return $this->countryCode;
    }

    public function getAvailability(): string
    {
        return $this->availability;
    }
}
