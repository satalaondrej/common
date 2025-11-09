<?php
declare(strict_types=1);

namespace Nalgoo\Common\Tests\Unit\Application\Middleware\Exceptions;

use Nalgoo\Common\Application\Exceptions\ApplicationException;
use Nalgoo\Common\Application\Exceptions\AuthorizationException;
use Nalgoo\Common\Application\Middleware\Exceptions\ApiKeyMiddlewareException;
use Nalgoo\Common\Application\Middleware\Exceptions\ApiKeyNotSetException;
use PHPUnit\Framework\TestCase;

final class ApiKeyNotSetExceptionTest extends TestCase
{
	public function testConstructor(): void
	{
		$exception = new ApiKeyNotSetException('API key not set');

		$this->assertInstanceOf(ApiKeyNotSetException::class, $exception);
		$this->assertInstanceOf(ApiKeyMiddlewareException::class, $exception);
		$this->assertInstanceOf(AuthorizationException::class, $exception);
		$this->assertInstanceOf(ApplicationException::class, $exception);
		$this->assertSame('API key not set', $exception->getMessage());
	}

	public function testConstructorWithCode(): void
	{
		$exception = new ApiKeyNotSetException('API key not set', 401);

		$this->assertSame('API key not set', $exception->getMessage());
		$this->assertSame(401, $exception->getCode());
	}

	public function testConstructorWithPrevious(): void
	{
		$previous = new \RuntimeException('Header missing');
		$exception = new ApiKeyNotSetException('API key not set', 0, $previous);

		$this->assertSame($previous, $exception->getPrevious());
	}
}
