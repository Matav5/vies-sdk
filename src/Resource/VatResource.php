<?php

declare(strict_types=1);

namespace Matav5\ViesSdk\Resource;

use Matav5\ViesSdk\Exception\ApiException;
use Matav5\ViesSdk\Exception\ViesSdkException;
use Matav5\ViesSdk\Request\CheckVatRequest;
use Matav5\ViesSdk\Response\CheckVatResponse;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class VatResource
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
        try {
            $response = $this->client->request('POST', $url, [
                'json' => $body,
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

        return CheckVatResponse::fromArray($data);
    }
}
