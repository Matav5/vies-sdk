<?php

declare(strict_types=1);

namespace Matav5\ViesSdk\Tests\Response;

use Matav5\ViesSdk\Response\StatusInformationResponse;
use PHPUnit\Framework\TestCase;

class StatusInformationResponseTest extends TestCase
{
    public function testFromArrayWithVowAvailableAndCountries(): void
    {
        $response = StatusInformationResponse::fromArray([
            'vow' => ['available' => true],
            'countries' => [
                ['countryCode' => 'CZ', 'availability' => 'Available'],
                ['countryCode' => 'DE', 'availability' => 'Unavailable'],
                ['countryCode' => 'SK', 'availability' => 'Monitoring Disabled'],
            ],
        ]);

        self::assertTrue($response->isVowAvailable());
        self::assertCount(3, $response->getCountries());

        $countries = $response->getCountries();
        self::assertSame('CZ', $countries[0]->getCountryCode());
        self::assertSame('Available', $countries[0]->getAvailability());
        self::assertSame('DE', $countries[1]->getCountryCode());
        self::assertSame('Unavailable', $countries[1]->getAvailability());
        self::assertSame('SK', $countries[2]->getCountryCode());
        self::assertSame('Monitoring Disabled', $countries[2]->getAvailability());
    }

    public function testFromArrayWithVowUnavailable(): void
    {
        $response = StatusInformationResponse::fromArray([
            'vow' => ['available' => false],
            'countries' => [],
        ]);

        self::assertFalse($response->isVowAvailable());
        self::assertSame([], $response->getCountries());
    }

    public function testFromArrayWithMissingFieldsDefaultsGracefully(): void
    {
        $response = StatusInformationResponse::fromArray([]);

        self::assertFalse($response->isVowAvailable());
        self::assertSame([], $response->getCountries());
    }
}
