<?php

declare(strict_types=1);

namespace Matav5\ViesSdk\Tests\Response;

use Matav5\ViesSdk\Enum\MatchStatus;
use Matav5\ViesSdk\Response\CheckVatResponse;
use PHPUnit\Framework\TestCase;

class CheckVatResponseTest extends TestCase
{
    public function testFromArrayMinimal(): void
    {
        $response = CheckVatResponse::fromArray([
            'valid' => true,
            'countryCode' => 'CZ',
            'vatNumber' => '27082440',
        ]);

        self::assertTrue($response->isValid());
        self::assertSame('CZ', $response->getCountryCode());
        self::assertSame('27082440', $response->getVatNumber());
        self::assertNull($response->getName());
        self::assertNull($response->getAddress());
        self::assertNull($response->getRequestDate());
        self::assertNull($response->getRequestIdentifier());
        self::assertNull($response->getTraderName());
        self::assertNull($response->getTraderNameMatch());
    }

    public function testFromArrayInvalidVat(): void
    {
        $response = CheckVatResponse::fromArray([
            'valid' => false,
            'countryCode' => 'CZ',
            'vatNumber' => '00000000',
        ]);

        self::assertFalse($response->isValid());
    }

    public function testFromArrayUsesValidKeyNotIsValid(): void
    {
        // Ensure the key is 'valid', not 'isValid' (old bug)
        $response = CheckVatResponse::fromArray(['isValid' => true, 'valid' => false]);

        self::assertFalse($response->isValid());
    }

    public function testFromArrayWithAllFields(): void
    {
        $response = CheckVatResponse::fromArray([
            'valid' => true,
            'countryCode' => 'CZ',
            'vatNumber' => '27082440',
            'requestDate' => '2024-01-01T00:00:00Z',
            'requestIdentifier' => 'req-123',
            'name' => 'Firma s.r.o.',
            'address' => 'Hlavní 1, Praha',
            'traderName' => 'Firma s.r.o.',
            'traderStreet' => 'Hlavní 1',
            'traderPostalCode' => '110 00',
            'traderCity' => 'Praha',
            'traderCompanyType' => 's.r.o.',
            'traderNameMatch' => 'VALID',
            'traderStreetMatch' => 'INVALID',
            'traderPostalCodeMatch' => 'NOT_PROCESSED',
            'traderCityMatch' => 'VALID',
            'traderCompanyTypeMatch' => 'NOT_PROCESSED',
        ]);

        self::assertTrue($response->isValid());
        self::assertSame('req-123', $response->getRequestIdentifier());
        self::assertSame('Firma s.r.o.', $response->getName());
        self::assertSame('Hlavní 1, Praha', $response->getAddress());
        self::assertSame('2024-01-01T00:00:00Z', $response->getRequestDate());
        self::assertSame('Firma s.r.o.', $response->getTraderName());
        self::assertSame('Hlavní 1', $response->getTraderStreet());
        self::assertSame('110 00', $response->getTraderPostalCode());
        self::assertSame('Praha', $response->getTraderCity());
        self::assertSame('s.r.o.', $response->getTraderCompanyType());
        self::assertSame(MatchStatus::VALID, $response->getTraderNameMatch());
        self::assertSame(MatchStatus::INVALID, $response->getTraderStreetMatch());
        self::assertSame(MatchStatus::NOT_PROCESSED, $response->getTraderPostalCodeMatch());
        self::assertSame(MatchStatus::VALID, $response->getTraderCityMatch());
        self::assertSame(MatchStatus::NOT_PROCESSED, $response->getTraderCompanyTypeMatch());
    }

    public function testFromArrayWithUnknownMatchStatusReturnsNull(): void
    {
        $response = CheckVatResponse::fromArray([
            'traderNameMatch' => 'BOGUS_VALUE',
        ]);

        self::assertNull($response->getTraderNameMatch());
    }

    public function testFromArrayWithMissingFieldsDefaultsGracefully(): void
    {
        $response = CheckVatResponse::fromArray([]);

        self::assertFalse($response->isValid());
        self::assertSame('', $response->getCountryCode());
        self::assertSame('', $response->getVatNumber());
    }
}
