<?php

declare(strict_types=1);

namespace Matav5\ViesSdk;

use Matav5\ViesSdk\Resource\StatusResource;
use Matav5\ViesSdk\Resource\VatResource;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class ViesClient
{
    private readonly VatResource $vat;
    private readonly StatusResource $status;

    public function __construct(
        ClientInterface $httpClient,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory,
        private readonly Config $config = new Config(),
    ) {
        $this->vat = new VatResource(
            $httpClient,
            $requestFactory,
            $streamFactory,
            $this->config->getBaseUrl(),
        );
        $this->status = new StatusResource(
            $httpClient,
            $requestFactory,
            $this->config->getBaseUrl(),
        );
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
