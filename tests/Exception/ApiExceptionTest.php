<?php

declare(strict_types=1);

namespace Matav5\ViesSdk\Tests\Exception;

use Matav5\ViesSdk\Exception\ApiException;
use PHPUnit\Framework\TestCase;

class ApiExceptionTest extends TestCase
{
    public function testGetStatusCode(): void
    {
        $exception = new ApiException('error', 400);

        self::assertSame(400, $exception->getStatusCode());
    }

    public function testGetErrorCodesWithNoWrappers(): void
    {
        $exception = new ApiException('error', 500);

        self::assertSame([], $exception->getErrorCodes());
    }

    public function testGetErrorCodesExtractsErrorField(): void
    {
        $exception = new ApiException(
            message: 'error',
            statusCode: 400,
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
            statusCode: 400,
            errorWrappers: [['message' => 'no error key here']],
        );

        self::assertSame([''], $exception->getErrorCodes());
    }

    public function testExceptionCodeMatchesStatusCode(): void
    {
        $exception = new ApiException('error', 503);

        self::assertSame(503, $exception->getCode());
        self::assertSame(503, $exception->getStatusCode());
    }
}
