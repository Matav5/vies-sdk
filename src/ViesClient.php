<?php

declare(strict_types=1);

namespace Matav5\ViesSdk;

use Matav5\ViesSdk\Resource\StatusResource;
use Matav5\ViesSdk\Resource\VatResource;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ViesClient
{
    private readonly VatResource $vat;
    private readonly StatusResource $status;

    public function __construct(
        HttpClientInterface $httpClient,
        private readonly Config $config = new Config(),
    ) {
        $client = $httpClient->withOptions(['timeout' => $this->config->getTimeout()]);

        $this->vat = new VatResource($client, $this->config->getBaseUrl());
        $this->status = new StatusResource($client, $this->config->getBaseUrl());
    }

    public function vat(): VatResource
    {
        return $this->vat;
    }

    public function status(): StatusResource
    {
        return $this->status;
    }
}
