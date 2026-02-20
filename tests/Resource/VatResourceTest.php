<?php

declare(strict_types=1);

namespace Matav5\ViesSdk\Tests\Resource;

use Matav5\ViesSdk\Enum\MatchStatus;
use Matav5\ViesSdk\Exception\ApiException;
use Matav5\ViesSdk\Request\CheckVatRequest;
use Matav5\ViesSdk\Resource\VatResource;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\HttpClient\Response\MockResponse;

class VatResourceTest extends TestCase
{
    private const BASE_URL = 'https://ec.europa.eu/taxation_customs/vies/rest-api';

    private function makeResource(MockResponse ...$responses): VatResource
    {
        $psr17 = new Psr17Factory();
        $client = new Psr18Client(new MockHttpClient($responses), $psr17, $psr17);

        return new VatResource($client, $client, $client, self::BASE_URL);
    }

    private function jsonResponse(array $data, int $status = 200): MockResponse
    {
        return new MockResponse(json_encode($data), [
            'http_code' => $status,
            'response_headers' => ['Content-Type: application/json'],
        ]);
    }

    public function testCheckSendsPostToCorrectUrl(): void
    {
        $mockResponse = $this->jsonResponse([
            'valid' => true,
            'countryCode' => 'CZ',
            'vatNumber' => '27082440',
        ]);

        $resource = $this->makeResource($mockResponse);
        $response = $resource->check(new CheckVatRequest('CZ', '27082440'));

        self::assertTrue($response->isValid());
        self::assertSame('CZ', $response->getCountryCode());
        self::assertSame('27082440', $response->getVatNumber());
        self::assertSame('POST', $mockResponse->getRequestMethod());
        self::assertStringEndsWith('/check-vat-number', $mockResponse->getRequestUrl());
    }

    public function testCheckSendsJsonBody(): void
    {
        $mockResponse = $this->jsonResponse([
            'valid' => false,
            'countryCode' => 'CZ',
            'vatNumber' => '00000000',
        ]);

        $resource = $this->makeResource($mockResponse);
        $resource->check(new CheckVatRequest('CZ', '00000000'));

        $body = json_decode($mockResponse->getRequestOptions()['body'], true);
        self::assertSame('CZ', $body['countryCode']);
        self::assertSame('00000000', $body['vatNumber']);
    }

    public function testCheckWithTraderFields(): void
    {
        $mockResponse = $this->jsonResponse([
            'valid' => true,
            'countryCode' => 'CZ',
            'vatNumber' => '27082440',
            'traderNameMatch' => 'VALID',
        ]);

        $resource = $this->makeResource($mockResponse);
        $response = $resource->check(new CheckVatRequest(
            countryCode: 'CZ',
            vatNumber: '27082440',
            traderName: 'Firma s.r.o.',
        ));

        self::assertSame(MatchStatus::VALID, $response->getTraderNameMatch());

        $body = json_decode($mockResponse->getRequestOptions()['body'], true);
        self::assertSame('Firma s.r.o.', $body['traderName']);
    }

    public function testCheckTestSendsToTestServiceUrl(): void
    {
        $mockResponse = $this->jsonResponse([
            'valid' => true,
            'countryCode' => 'CZ',
            'vatNumber' => '100',
        ]);

        $resource = $this->makeResource($mockResponse);
        $response = $resource->checkTest(new CheckVatRequest('CZ', '100'));

        self::assertTrue($response->isValid());
        self::assertStringEndsWith('/check-vat-test-service', $mockResponse->getRequestUrl());
    }

    public function testCheckThrowsApiExceptionOn400(): void
    {
        $this->expectException(ApiException::class);
        $this->expectExceptionCode(400);

        $resource = $this->makeResource($this->jsonResponse([
            'actionSucceed' => false,
            'errorWrappers' => [
                ['error' => 'INVALID_INPUT', 'message' => 'Country code is invalid'],
            ],
        ], 400));

        $resource->check(new CheckVatRequest('XX', '123'));
    }

    public function testCheckApiExceptionContainsErrorCodes(): void
    {
        $resource = $this->makeResource($this->jsonResponse([
            'actionSucceed' => false,
            'errorWrappers' => [
                ['error' => 'INVALID_INPUT'],
                ['error' => 'VAT_BLOCKED'],
            ],
        ], 400));

        try {
            $resource->check(new CheckVatRequest('XX', '123'));
            self::fail('Expected ApiException');
        } catch (ApiException $e) {
            self::assertSame(['INVALID_INPUT', 'VAT_BLOCKED'], $e->getErrorCodes());
        }
    }

    public function testCheckThrowsApiExceptionOn500(): void
    {
        $this->expectException(ApiException::class);
        $this->expectExceptionCode(500);

        $resource = $this->makeResource($this->jsonResponse([
            'actionSucceed' => false,
            'errorWrappers' => [],
        ], 500));

        $resource->check(new CheckVatRequest('CZ', '27082440'));
    }
}
