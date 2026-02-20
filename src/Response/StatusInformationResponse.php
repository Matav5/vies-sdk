<?php

declare(strict_types=1);

namespace Matav5\ViesSdk\Response;

class StatusInformationResponse
{
    /**
     * @param CountryStatus[] $countries
     */
    public function __construct(
        private readonly bool $vowAvailable,
        private readonly array $countries,
    ) {
    }

    public static function fromArray(array $data): self
    {
        $countries = [];
        foreach ($data['countries'] ?? [] as $country) {
            $countries[] = new CountryStatus(
                countryCode: $country['countryCode'] ?? '',
                availability: $country['availability'] ?? '',
            );
        }

        return new self(
            vowAvailable: (bool) ($data['vow']['available'] ?? false),
            countries: $countries,
        );
    }

    public function isVowAvailable(): bool
    {
        return $this->vowAvailable;
    }

    /**
     * @return CountryStatus[]
     */
    public function getCountries(): array
    {
        return $this->countries;
    }
}
