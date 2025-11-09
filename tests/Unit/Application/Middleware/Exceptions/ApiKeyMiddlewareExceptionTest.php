<?php
declare(strict_types=1);

namespace Nalgoo\Common\Tests\Unit\Application\Middleware\Exceptions;

use Nalgoo\Common\Application\Exceptions\ApplicationException;
use Nalgoo\Common\Application\Exceptions\AuthorizationException;
use Nalgoo\Common\Application\Middleware\Exceptions\ApiKeyMiddlewareException;
use PHPUnit\Framework\TestCase;

final class ApiKeyMiddlewareExceptionTest extends TestCase
{
	public function testConstructor(): void
	{
		$exception = new ApiKeyMiddlewareException('API key middleware error');

		$this->assertInstanceOf(ApiKeyMiddlewareException::class, $exception);
		$this->assertInstanceOf(AuthorizationException::class, $exception);
		$this->assertInstanceOf(ApplicationException::class, $exception);
		$this->assertSame('API key middleware error', $exception->getMessage());
	}

	public function testConstructorWithCode(): void
	{
		$exception = new ApiKeyMiddlewareException('API key middleware error', 401);

		$this->assertSame('API key middleware error', $exception->getMessage());
		$this->assertSame(401, $exception->getCode());
	}

	public function testConstructorWithPrevious(): void
	{
		$previous = new \RuntimeException('Configuration error');
		$exception = new ApiKeyMiddlewareException('API key middleware error', 0, $previous);

		$this->assertSame($previous, $exception->getPrevious());
	}
}
