<?php

declare(strict_types=1);

namespace Matav5\ViesSdk\Response;

use Matav5\ViesSdk\Enum\MatchStatus;

class CheckVatResponse
{
    public function __construct(
        private readonly bool $valid,
        private readonly string $countryCode,
        private readonly string $vatNumber,
        private readonly ?string $name,
        private readonly ?string $address,
        private readonly ?string $requestDate,
        private readonly ?string $requestIdentifier,
        private readonly ?string $traderName,
        private readonly ?string $traderStreet,
        private readonly ?string $traderPostalCode,
        private readonly ?string $traderCity,
        private readonly ?string $traderCompanyType,
        private readonly ?MatchStatus $traderNameMatch,
        private readonly ?MatchStatus $traderStreetMatch,
        private readonly ?MatchStatus $traderPostalCodeMatch,
        private readonly ?MatchStatus $traderCityMatch,
        private readonly ?MatchStatus $traderCompanyTypeMatch,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            valid: (bool) ($data['valid'] ?? false),
            countryCode: $data['countryCode'] ?? '',
            vatNumber: $data['vatNumber'] ?? '',
            name: $data['name'] ?? null,
            address: $data['address'] ?? null,
            requestDate: $data['requestDate'] ?? null,
            requestIdentifier: $data['requestIdentifier'] ?? null,
            traderName: $data['traderName'] ?? null,
            traderStreet: $data['traderStreet'] ?? null,
            traderPostalCode: $data['traderPostalCode'] ?? null,
            traderCity: $data['traderCity'] ?? null,
            traderCompanyType: $data['traderCompanyType'] ?? null,
            traderNameMatch: isset($data['traderNameMatch']) ? MatchStatus::tryFrom($data['traderNameMatch']) : null,
            traderStreetMatch: isset($data['traderStreetMatch']) ? MatchStatus::tryFrom($data['traderStreetMatch']) : null,
            traderPostalCodeMatch: isset($data['traderPostalCodeMatch']) ? MatchStatus::tryFrom($data['traderPostalCodeMatch']) : null,
            traderCityMatch: isset($data['traderCityMatch']) ? MatchStatus::tryFrom($data['traderCityMatch']) : null,
            traderCompanyTypeMatch: isset($data['traderCompanyTypeMatch']) ? MatchStatus::tryFrom($data['traderCompanyTypeMatch']) : null,
        );
    }

    public function isValid(): bool
    {
        return $this->valid;
    }

    public function getCountryCode(): string
    {
        return $this->countryCode;
    }

    public function getVatNumber(): string
    {
        return $this->vatNumber;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function getRequestDate(): ?string
    {
        return $this->requestDate;
    }

    public function getRequestIdentifier(): ?string
    {
        return $this->requestIdentifier;
    }

    public function getTraderName(): ?string
    {
        return $this->traderName;
    }

    public function getTraderStreet(): ?string
    {
        return $this->traderStreet;
    }

    public function getTraderPostalCode(): ?string
    {
        return $this->traderPostalCode;
    }

    public function getTraderCity(): ?string
    {
        return $this->traderCity;
    }

    public function getTraderCompanyType(): ?string
    {
        return $this->traderCompanyType;
    }

    public function getTraderNameMatch(): ?MatchStatus
    {
        return $this->traderNameMatch;
    }

    public function getTraderStreetMatch(): ?MatchStatus
    {
        return $this->traderStreetMatch;
    }

    public function getTraderPostalCodeMatch(): ?MatchStatus
    {
        return $this->traderPostalCodeMatch;
    }

    public function getTraderCityMatch(): ?MatchStatus
    {
        return $this->traderCityMatch;
    }

    public function getTraderCompanyTypeMatch(): ?MatchStatus
    {
        return $this->traderCompanyTypeMatch;
    }
}
