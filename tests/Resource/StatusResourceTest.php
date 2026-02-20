<?php

declare(strict_types=1);

namespace Matav5\ViesSdk\Tests\Resource;

use Matav5\ViesSdk\Exception\ApiException;
use Matav5\ViesSdk\Resource\StatusResource;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class StatusResourceTest extends TestCase
{
    private const BASE_URL = 'https://ec.europa.eu/taxation_customs/vies/rest-api';

    private function makeResource(MockResponse ...$responses): StatusResource
    {
        return new StatusResource(new MockHttpClient($responses), self::BASE_URL);
    }

    private function jsonResponse(array $data, int $status = 200): MockResponse
    {
        return new MockResponse(json_encode($data), [
            'http_code' => $status,
            'response_headers' => ['Content-Type: application/json'],
        ]);
    }

    public function testCheckSendsGetToCorrectUrl(): void
    {
        $mockResponse = $this->jsonResponse([
            'vow' => ['available' => true],
            'countries' => [
                ['countryCode' => 'CZ', 'availability' => 'Available'],
            ],
        ]);

        $resource = $this->makeResource($mockResponse);
        $response = $resource->check();

        self::assertSame('GET', $mockResponse->getRequestMethod());
        self::assertStringEndsWith('/check-status', $mockResponse->getRequestUrl());
        self::assertTrue($response->isVowAvailable());
    }

    public function testCheckReturnsCountries(): void
    {
        $resource = $this->makeResource($this->jsonResponse([
            'vow' => ['available' => true],
            'countries' => [
                ['countryCode' => 'CZ', 'availability' => 'Available'],
                ['countryCode' => 'HU', 'availability' => 'Unavailable'],
                ['countryCode' => 'PL', 'availability' => 'Monitoring Disabled'],
            ],
        ]));

        $response = $resource->check();
        $countries = $response->getCountries();

        self::assertCount(3, $countries);
        self::assertSame('CZ', $countries[0]->getCountryCode());
        self::assertSame('Available', $countries[0]->getAvailability());
        self::assertSame('HU', $countries[1]->getCountryCode());
        self::assertSame('Unavailable', $countries[1]->getAvailability());
        self::assertSame('PL', $countries[2]->getCountryCode());
        self::assertSame('Monitoring Disabled', $countries[2]->getAvailability());
    }

    public function testCheckThrowsApiExceptionOnError(): void
    {
        $this->expectException(ApiException::class);
        $this->expectExceptionCode(500);

        $resource = $this->makeResource($this->jsonResponse([
            'actionSucceed' => false,
            'errorWrappers' => [],
        ], 500));

        $resource->check();
    }

    public function testPingReturnsTrueWhenVowAvailable(): void
    {
        $resource = $this->makeResource($this->jsonResponse([
            'vow' => ['available' => true],
            'countries' => [],
        ]));

        self::assertTrue($resource->ping());
    }

    public function testPingReturnsFalseWhenVowUnavailable(): void
    {
        $resource = $this->makeResource($this->jsonResponse([
            'vow' => ['available' => false],
            'countries' => [],
        ]));

        self::assertFalse($resource->ping());
    }

    public function testPingReturnsFalseOnNetworkError(): void
    {
        $resource = $this->makeResource(new MockResponse('', ['error' => 'Connection refused']));

        self::assertFalse($resource->ping());
    }

    public function testPingReturnsFalseOnApiError(): void
    {
        $resource = $this->makeResource($this->jsonResponse([
            'actionSucceed' => false,
            'errorWrappers' => [],
        ], 500));

        self::assertFalse($resource->ping());
    }
}
