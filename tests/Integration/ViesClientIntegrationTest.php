<?php

declare(strict_types=1);

namespace Matav5\ViesSdk\Tests\Integration;

use Matav5\ViesSdk\Enum\MatchStatus;
use Matav5\ViesSdk\Exception\ApiException;
use Matav5\ViesSdk\Request\CheckVatRequest;
use Matav5\ViesSdk\ViesClient;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\HttpClient;

#[Group('integration')]
class ViesClientIntegrationTest extends TestCase
{
    private ViesClient $vies;

    protected function setUp(): void
    {
        $this->vies = new ViesClient(HttpClient::create());
    }

    public function testCheckTestServiceReturnsValidForKnownValidNumber(): void
    {
        $response = $this->vies->vat()->checkTest(new CheckVatRequest('CZ', '100'));

        self::assertTrue($response->isValid());
        self::assertSame('CZ', $response->getCountryCode());
        self::assertSame('100', $response->getVatNumber());
        self::assertNotNull($response->getName());
    }

    public function testCheckTestServiceReturnsInvalidForKnownInvalidNumber(): void
    {
        $response = $this->vies->vat()->check(new CheckVatRequest('CZ', '200'));

        self::assertFalse($response->isValid());
    }

    public function testCheckRealVatNumber(): void
    {
        $response = $this->vies->vat()->check(new CheckVatRequest('CZ', '27082440'));

        self::assertTrue($response->isValid());
        self::assertSame('CZ', $response->getCountryCode());
        self::assertSame('27082440', $response->getVatNumber());
        self::assertStringContainsStringIgnoringCase('alza', $response->getName() ?? '');
    }

    public function testCheckWithTraderNameReturnMatchStatus(): void
    {
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
        $response = $this->vies->vat()->check(new CheckVatRequest('XX', '123456'));

        self::assertFalse($response->isValid());
    }

    public function testCheckEmptyVatNumberThrowsApiException(): void
    {
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

        self::assertGreaterThanOrEqual(27, count($response->getCountries()));

        $codes = array_map(fn($c) => $c->getCountryCode(), $response->getCountries());

        self::assertContains('CZ', $codes);
        self::assertContains('DE', $codes);
        self::assertContains('FR', $codes);
    }

    public function testStatusCheckVowAvailability(): void
    {
        $response = $this->vies->status()->check();

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
                sprintf('Country %s has unexpected availability: %s', $country->getCountryCode(), $country->getAvailability()),
            );
        }
    }
}
