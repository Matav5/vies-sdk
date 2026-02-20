<?php

declare(strict_types=1);

namespace Matav5\ViesSdk\Resource;

use Matav5\ViesSdk\Exception\ApiException;
use Matav5\ViesSdk\Exception\ViesSdkException;
use Matav5\ViesSdk\Request\CheckVatRequest;
use Matav5\ViesSdk\Response\CheckVatResponse;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class VatResource
{
    public function __construct(
        private readonly ClientInterface $httpClient,
        private readonly RequestFactoryInterface $requestFactory,
        private readonly StreamFactoryInterface $streamFactory,
        private readonly string $baseUrl,
    ) {
    }

    /**
     * @throws ApiException
     * @throws ViesSdkException
     */
    public function check(CheckVatRequest $request): CheckVatResponse
    {
        return $this->sendPost($this->baseUrl . '/check-vat-number', $request->toArray());
    }

    /**
     * @throws ApiException
     * @throws ViesSdkException
     */
    public function checkTest(CheckVatRequest $request): CheckVatResponse
    {
        return $this->sendPost($this->baseUrl . '/check-vat-test-service', $request->toArray());
    }

    /**
     * @throws ApiException
     * @throws ViesSdkException
     */
    private function sendPost(string $url, array $body): CheckVatResponse
    {
        $json = json_encode($body);
        $stream = $this->streamFactory->createStream($json);

        $httpRequest = $this->requestFactory
            ->createRequest('POST', $url)
            ->withHeader('Accept', 'application/json')
            ->withHeader('Content-Type', 'application/json')
            ->withBody($stream);

        try {
            $response = $this->httpClient->sendRequest($httpRequest);
        } catch (ClientExceptionInterface $e) {
            throw new ViesSdkException('HTTP request failed: ' . $e->getMessage(), 0, $e);
        }

        $data = json_decode((string) $response->getBody(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new ViesSdkException('Failed to decode API response: ' . json_last_error_msg());
        }

        if ($response->getStatusCode() !== 200) {
            $errorWrappers = $data['errorWrappers'] ?? [];
            throw new ApiException(
                sprintf('VIES API returned status %d', $response->getStatusCode()),
                $response,
                $response->getStatusCode(),
                $errorWrappers,
            );
        }

        return CheckVatResponse::fromArray($data);
    }
}
