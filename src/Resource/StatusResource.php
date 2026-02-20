<?php

declare(strict_types=1);

namespace Matav5\ViesSdk\Resource;

use Matav5\ViesSdk\Exception\ApiException;
use Matav5\ViesSdk\Exception\ViesSdkException;
use Matav5\ViesSdk\Response\StatusInformationResponse;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class StatusResource
{
    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly string $baseUrl,
    ) {
    }

    /**
     * @throws ApiException
     * @throws ViesSdkException
     */
    public function check(): StatusInformationResponse
    {
        try {
            $response = $this->client->request('GET', $this->baseUrl . '/check-status', [
                'headers' => ['Accept' => 'application/json'],
            ]);
            $statusCode = $response->getStatusCode();
            $data = $response->toArray(false);
        } catch (DecodingExceptionInterface $e) {
            throw new ViesSdkException('Failed to decode API response: ' . $e->getMessage(), 0, $e);
        } catch (TransportExceptionInterface $e) {
            throw new ViesSdkException('HTTP request failed: ' . $e->getMessage(), 0, $e);
        }

        if ($statusCode !== 200) {
            throw new ApiException(
                sprintf('VIES API returned status %d', $statusCode),
                $statusCode,
                $data['errorWrappers'] ?? [],
            );
        }

        return StatusInformationResponse::fromArray($data);
    }

    /**
     * Returns true if the VIES API is reachable and the VOW service is available.
     * Returns false on any error instead of throwing.
     */
    public function ping(): bool
    {
        try {
            return $this->check()->isVowAvailable();
        } catch (ViesSdkException) {
            return false;
        }
    }
}
