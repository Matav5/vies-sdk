<?php

declare(strict_types=1);

namespace Matav5\ViesSdk\Request;

class CheckVatRequest
{
    public function __construct(
        private readonly string $countryCode,
        private readonly string $vatNumber,
        private readonly ?string $requesterMemberStateCode = null,
        private readonly ?string $requesterNumber = null,
        private readonly ?string $traderName = null,
        private readonly ?string $traderStreet = null,
        private readonly ?string $traderPostalCode = null,
        private readonly ?string $traderCity = null,
        private readonly ?string $traderCompanyType = null,
    ) {
    }

    public function getCountryCode(): string
    {
        return strtoupper($this->countryCode);
    }

    public function getVatNumber(): string
    {
        return $this->vatNumber;
    }

    public function toArray(): array
    {
        $data = [
            'countryCode' => $this->getCountryCode(),
            'vatNumber' => $this->vatNumber,
        ];

        if ($this->requesterMemberStateCode !== null) {
            $data['requesterMemberStateCode'] = $this->requesterMemberStateCode;
        }
        if ($this->requesterNumber !== null) {
            $data['requesterNumber'] = $this->requesterNumber;
        }
        if ($this->traderName !== null) {
            $data['traderName'] = $this->traderName;
        }
        if ($this->traderStreet !== null) {
            $data['traderStreet'] = $this->traderStreet;
        }
        if ($this->traderPostalCode !== null) {
            $data['traderPostalCode'] = $this->traderPostalCode;
        }
        if ($this->traderCity !== null) {
            $data['traderCity'] = $this->traderCity;
        }
        if ($this->traderCompanyType !== null) {
            $data['traderCompanyType'] = $this->traderCompanyType;
        }

        return $data;
    }
}
