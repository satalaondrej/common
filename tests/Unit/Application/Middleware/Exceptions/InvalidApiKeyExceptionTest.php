<?php
declare(strict_types=1);

namespace Nalgoo\Common\Tests\Unit\Application\Middleware\Exceptions;

use Nalgoo\Common\Application\Exceptions\ApplicationException;
use Nalgoo\Common\Application\Exceptions\AuthorizationException;
use Nalgoo\Common\Application\Middleware\Exceptions\ApiKeyMiddlewareException;
use Nalgoo\Common\Application\Middleware\Exceptions\InvalidApiKeyException;
use PHPUnit\Framework\TestCase;

final class InvalidApiKeyExceptionTest extends TestCase
{
	public function testConstructor(): void
	{
		$exception = new InvalidApiKeyException('Invalid API key');

		$this->assertInstanceOf(InvalidApiKeyException::class, $exception);
		$this->assertInstanceOf(ApiKeyMiddlewareException::class, $exception);
		$this->assertInstanceOf(AuthorizationException::class, $exception);
		$this->assertInstanceOf(ApplicationException::class, $exception);
		$this->assertSame('Invalid API key', $exception->getMessage());
	}

	public function testConstructorWithCode(): void
	{
		$exception = new InvalidApiKeyException('Invalid API key', 403);

		$this->assertSame('Invalid API key', $exception->getMessage());
		$this->assertSame(403, $exception->getCode());
	}

	public function testConstructorWithPrevious(): void
	{
		$previous = new \RuntimeException('Verification failed');
		$exception = new InvalidApiKeyException('Invalid API key', 0, $previous);

		$this->assertSame($previous, $exception->getPrevious());
	}
}
