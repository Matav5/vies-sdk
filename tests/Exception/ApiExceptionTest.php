<?php

declare(strict_types=1);

namespace Matav5\ViesSdk\Tests\Exception;

use Matav5\ViesSdk\Exception\ApiException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class ApiExceptionTest extends TestCase
{
    private function makeResponse(int $statusCode): ResponseInterface
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn($statusCode);

        return $response;
    }

    public function testGetStatusCode(): void
    {
        $exception = new ApiException('error', $this->makeResponse(400), 400);

        self::assertSame(400, $exception->getStatusCode());
    }

    public function testGetErrorCodesWithNoWrappers(): void
    {
        $exception = new ApiException('error', $this->makeResponse(500), 500);

        self::assertSame([], $exception->getErrorCodes());
    }

    public function testGetErrorCodesExtractsErrorField(): void
    {
        $exception = new ApiException(
            message: 'error',
            response: $this->makeResponse(400),
            code: 400,
            errorWrappers: [
                ['error' => 'INVALID_INPUT', 'message' => 'Country code is invalid'],
                ['error' => 'VAT_BLOCKED'],
            ],
        );

        self::assertSame(['INVALID_INPUT', 'VAT_BLOCKED'], $exception->getErrorCodes());
    }

    public function testGetErrorCodesHandlesMissingErrorKey(): void
    {
        $exception = new ApiException(
            message: 'error',
            response: $this->makeResponse(400),
            code: 400,
            errorWrappers: [['message' => 'no error key here']],
        );

        self::assertSame([''], $exception->getErrorCodes());
    }

    public function testGetResponse(): void
    {
        $response = $this->makeResponse(500);
        $exception = new ApiException('error', $response, 500);

        self::assertSame($response, $exception->getResponse());
    }
}
