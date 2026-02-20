<?php

declare(strict_types=1);

namespace Matav5\ViesSdk\Tests\Integration;

use Matav5\ViesSdk\Enum\MatchStatus;
use Matav5\ViesSdk\Exception\ApiException;
use Matav5\ViesSdk\Request\CheckVatRequest;
use Matav5\ViesSdk\ViesClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\Psr18Client;

#[Group('integration')]
class ViesClientIntegrationTest extends TestCase
{
    private ViesClient $vies;

    protected function setUp(): void
    {
        $psr17 = new Psr17Factory();
        $http = new Psr18Client(null, $psr17, $psr17);

        $this->vies = new ViesClient($http, $http, $http);
    }

    public function testCheckTestServiceReturnsValidForKnownValidNumber(): void
    {
        // Test number 100 is documented as returning valid
        $response = $this->vies->vat()->checkTest(new CheckVatRequest('CZ', '100'));

        self::assertTrue($response->isValid());
        self::assertSame('CZ', $response->getCountryCode());
        self::assertSame('100', $response->getVatNumber());
        self::assertNotNull($response->getName());
    }

    public function testCheckTestServiceReturnsInvalidForKnownInvalidNumber(): void
    {
        // Test number 200 is documented as returning invalid
        $response = $this->vies->vat()->check(new CheckVatRequest('CZ', '200'));

        self::assertFalse($response->isValid());
    }

    public function testCheckRealVatNumber(): void
    {
        // Alza.cz a.s. — well-known Czech company
        $response = $this->vies->vat()->check(new CheckVatRequest('CZ', '27082440'));

        self::assertTrue($response->isValid());
        self::assertSame('CZ', $response->getCountryCode());
        self::assertSame('27082440', $response->getVatNumber());
        self::assertStringContainsStringIgnoringCase('alza', $response->getName() ?? '');
    }

    public function testCheckWithTraderNameReturnMatchStatus(): void
    {
        // CZ does not support approximate matching — API returns NOT_PROCESSED for trader fields
        $request = new CheckVatRequest(
            countryCode: 'CZ',
            vatNumber: '27082440',
            traderName: 'Alza.cz a.s.',
        );

        $response = $this->vies->vat()->check($request);
        self::assertTrue($response->isValid());
        // CZ returns NOT_PROCESSED (member state does not support approximate matching)
        self::assertSame(MatchStatus::NOT_PROCESSED, $response->getTraderNameMatch());
    }

    public function testCheckWithUnknownCountryCodeReturnsInvalidWithoutException(): void
    {
        // VIES returns HTTP 200 with valid=false for unknown country codes (non-empty vatNumber)
        $response = $this->vies->vat()->check(new CheckVatRequest('XX', '123456'));

        self::assertFalse($response->isValid());
    }

    public function testCheckEmptyVatNumberThrowsApiException(): void
    {
        // Empty vatNumber triggers a 400 Bad Request from the API
        $this->expectException(ApiException::class);
        $this->expectExceptionCode(400);

        $this->vies->vat()->check(new CheckVatRequest('CZ', ''));
    }

    public function testCheckEmptyVatNumberApiExceptionContainsErrorCode(): void
    {
        try {
            $this->vies->vat()->check(new CheckVatRequest('CZ', ''));
            self::fail('Expected ApiException');
        } catch (ApiException $e) {
            self::assertSame(400, $e->getStatusCode());
            self::assertContains('VOW-ERR-11', $e->getErrorCodes());
        }
    }

    public function testStatusCheckReturnsAllMemberStates(): void
    {
        $response = $this->vies->status()->check();

        // EU has 27 member states + XI (Northern Ireland)
        self::assertGreaterThanOrEqual(27, count($response->getCountries()));

        $codes = array_map(
            fn($c) => $c->getCountryCode(),
            $response->getCountries(),
        );

        // Spot-check a few member states are present
        self::assertContains('CZ', $codes);
        self::assertContains('DE', $codes);
        self::assertContains('FR', $codes);
    }

    public function testStatusCheckVowAvailability(): void
    {
        $response = $this->vies->status()->check();

        // vowAvailable is a bool — just assert it's returned without error
        self::assertIsBool($response->isVowAvailable());
    }

    public function testStatusCheckCountryAvailabilityValues(): void
    {
        $response = $this->vies->status()->check();
        $validValues = ['Available', 'Unavailable', 'Monitoring Disabled'];

        foreach ($response->getCountries() as $country) {
            self::assertContains(
                $country->getAvailability(),
                $validValues,
                sprintf(
                    'Country %s has unexpected availability: %s',
                    $country->getCountryCode(),
                    $country->getAvailability(),
                ),
            );
        }
    }
}
