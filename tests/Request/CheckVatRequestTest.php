<?php

declare(strict_types=1);

namespace Matav5\ViesSdk\Tests\Request;

use Matav5\ViesSdk\Request\CheckVatRequest;
use PHPUnit\Framework\TestCase;

class CheckVatRequestTest extends TestCase
{
    public function testMinimalRequest(): void
    {
        $request = new CheckVatRequest('cz', '27082440');

        self::assertSame('CZ', $request->getCountryCode());
        self::assertSame('27082440', $request->getVatNumber());
    }

    public function testCountryCodeIsNormalizedToUppercase(): void
    {
        $request = new CheckVatRequest('de', '123456789');

        self::assertSame('DE', $request->getCountryCode());
    }

    public function testToArrayContainsRequiredFields(): void
    {
        $request = new CheckVatRequest('CZ', '27082440');

        self::assertSame([
            'countryCode' => 'CZ',
            'vatNumber' => '27082440',
        ], $request->toArray());
    }

    public function testToArrayOmitsNullOptionalFields(): void
    {
        $request = new CheckVatRequest('CZ', '27082440', traderName: 'Firma s.r.o.');
        $array = $request->toArray();

        self::assertArrayHasKey('traderName', $array);
        self::assertArrayNotHasKey('traderStreet', $array);
        self::assertArrayNotHasKey('traderCity', $array);
        self::assertArrayNotHasKey('requesterMemberStateCode', $array);
    }

    public function testToArrayWithAllFields(): void
    {
        $request = new CheckVatRequest(
            countryCode: 'CZ',
            vatNumber: '27082440',
            requesterMemberStateCode: 'SK',
            requesterNumber: 'SK1234567',
            traderName: 'Firma s.r.o.',
            traderStreet: 'Hlavní 1',
            traderPostalCode: '110 00',
            traderCity: 'Praha',
            traderCompanyType: 's.r.o.',
        );

        self::assertSame([
            'countryCode' => 'CZ',
            'vatNumber' => '27082440',
            'requesterMemberStateCode' => 'SK',
            'requesterNumber' => 'SK1234567',
            'traderName' => 'Firma s.r.o.',
            'traderStreet' => 'Hlavní 1',
            'traderPostalCode' => '110 00',
            'traderCity' => 'Praha',
            'traderCompanyType' => 's.r.o.',
        ], $request->toArray());
    }
}
