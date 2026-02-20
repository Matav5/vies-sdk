# VIES SDK

PHP SDK for the [EU VIES (VAT Information Exchange System)](https://ec.europa.eu/taxation_customs/vies/) REST API.

Validate EU VAT numbers and check the availability of member states via a clean, PSR-18 compatible HTTP client.

## Requirements

- PHP 8.1+
- A PSR-18 HTTP client (e.g. `symfony/http-client`)
- A PSR-17 request & stream factory (e.g. `nyholm/psr7`)

## Installation

```bash
composer require matav5/vies-sdk
```

You also need a PSR-18 client and PSR-17 factories. The recommended combination:

```bash
composer require symfony/http-client nyholm/psr7
```

## Usage

### Setup

```php
use Matav5\ViesSdk\ViesClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use Symfony\Component\HttpClient\Psr18Client;

$psr17 = new Psr17Factory();
$http  = new Psr18Client(null, $psr17, $psr17);

$vies = new ViesClient($http, $http, $http);
```

`Psr18Client` implements PSR-18, PSR-17 RequestFactory and PSR-17 StreamFactory simultaneously, which is why it is passed three times.

### Validate a VAT number

```php
use Matav5\ViesSdk\Request\CheckVatRequest;

$response = $vies->vat()->check(new CheckVatRequest('CZ', '27082440'));

$response->isValid();        // true
$response->getCountryCode(); // 'CZ'
$response->getVatNumber();   // '27082440'
$response->getName();        // 'Alza.cz a.s.'
$response->getAddress();     // 'Jankovcova 1522/53 ...'
$response->getRequestDate(); // '2024-01-01T00:00:00Z'
```

### Approximate trader matching

Pass optional trader fields to verify company details against the VIES registry. The API returns a `MatchStatus` for each field if the member state supports approximate matching.

```php
use Matav5\ViesSdk\Enum\MatchStatus;
use Matav5\ViesSdk\Request\CheckVatRequest;

$request = new CheckVatRequest(
    countryCode: 'DE',
    vatNumber:   '123456789',
    traderName:  'Example GmbH',
    traderCity:  'Berlin',
);

$response = $vies->vat()->check($request);

$response->getTraderNameMatch(); // MatchStatus::VALID | INVALID | NOT_PROCESSED | null
$response->getTraderCityMatch();
```

`NOT_PROCESSED` means the member state does not support approximate matching for that field.

To also receive a `requestIdentifier`, provide your own VAT number as the requester:

```php
$request = new CheckVatRequest(
    countryCode:              'DE',
    vatNumber:                '123456789',
    requesterMemberStateCode: 'CZ',
    requesterNumber:          'CZ27082440',
);

$response->getRequestIdentifier(); // 'WAPPws...'
```

### Test endpoint

The VIES API provides a test endpoint that works without a real VAT number:

```php
// vatNumber 100 → valid, 200 → invalid (documented test values)
$response = $vies->vat()->checkTest(new CheckVatRequest('CZ', '100'));
$response->isValid(); // true
```

### Member state availability

```php
$status = $vies->status()->check();

$status->isVowAvailable(); // true
$status->getCountries();   // array<CountryStatus>

foreach ($status->getCountries() as $country) {
    echo $country->getCountryCode();  // 'CZ'
    echo $country->getAvailability(); // 'Available' | 'Unavailable' | 'Monitoring Disabled'
}
```

### Error handling

```php
use Matav5\ViesSdk\Exception\ApiException;
use Matav5\ViesSdk\Exception\ViesSdkException;

try {
    $response = $vies->vat()->check(new CheckVatRequest('CZ', ''));
} catch (ApiException $e) {
    // HTTP error response from the VIES API
    $e->getStatusCode();  // 400
    $e->getErrorCodes();  // ['VOW-ERR-11']
    $e->getResponse();    // PSR-7 ResponseInterface
} catch (ViesSdkException $e) {
    // Network error or invalid JSON response
}
```

`ApiException` extends `ViesSdkException`, so you can catch all SDK errors with a single `catch (ViesSdkException $e)`.

### Custom configuration

```php
use Matav5\ViesSdk\Config;
use Matav5\ViesSdk\ViesClient;

$config = new Config(
    baseUrl: 'https://ec.europa.eu/taxation_customs/vies/rest-api',
    timeout: 10,
);

$vies = new ViesClient($http, $http, $http, $config);
```

## Running tests

```bash
# Unit tests only
composer test

# Integration tests (calls the real VIES API)
composer test:integration

# All tests
composer test:all
```

## License

MIT
