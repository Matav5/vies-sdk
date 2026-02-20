<?php

declare(strict_types=1);

namespace Matav5\ViesSdk\Resource;

use Matav5\ViesSdk\Exception\ApiException;
use Matav5\ViesSdk\Exception\ViesSdkException;
use Matav5\ViesSdk\Response\StatusInformationResponse;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;

class StatusResource
{
    public function __construct(
        private readonly ClientInterface $httpClient,
        private readonly RequestFactoryInterface $requestFactory,
        private readonly string $baseUrl,
    ) {
    }

    /**
     * @throws ApiException
     * @throws ViesSdkException
     */
    public function check(): StatusInformationResponse
    {
        $httpRequest = $this->requestFactory
            ->createRequest('GET', $this->baseUrl . '/check-status')
            ->withHeader('Accept', 'application/json');

        try {
            $response = $this->httpClient->sendRequest($httpRequest);
        } catch (ClientExceptionInterface $e) {
            throw new ViesSdkException('HTTP request failed: ' . $e->getMessage(), 0, $e);
        }

        if ($response->getStatusCode() !== 200) {
            $data = json_decode((string) $response->getBody(), true) ?? [];
            $errorWrappers = $data['errorWrappers'] ?? [];
            throw new ApiException(
                sprintf('VIES API returned status %d', $response->getStatusCode()),
                $response,
                $response->getStatusCode(),
                $errorWrappers,
            );
        }

        $data = json_decode((string) $response->getBody(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new ViesSdkException('Failed to decode API response: ' . json_last_error_msg());
        }

        return StatusInformationResponse::fromArray($data);
    }
}
