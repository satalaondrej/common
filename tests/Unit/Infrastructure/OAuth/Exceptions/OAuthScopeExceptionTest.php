<?php
declare(strict_types=1);

namespace Nalgoo\Common\Tests\Unit\Infrastructure\OAuth\Exceptions;

use Nalgoo\Common\Infrastructure\OAuth\Exceptions\OAuthException;
use Nalgoo\Common\Infrastructure\OAuth\Exceptions\OAuthScopeException;
use PHPUnit\Framework\TestCase;

final class OAuthScopeExceptionTest extends TestCase
{
	public function testConstructor(): void
	{
		$exception = new OAuthScopeException('Missing required scope');

		$this->assertInstanceOf(OAuthScopeException::class, $exception);
		$this->assertInstanceOf(OAuthException::class, $exception);
		$this->assertSame('Missing required scope', $exception->getMessage());
	}

	public function testConstructorWithCode(): void
	{
		$exception = new OAuthScopeException('Missing required scope', 403);

		$this->assertSame('Missing required scope', $exception->getMessage());
		$this->assertSame(403, $exception->getCode());
	}

	public function testConstructorWithPrevious(): void
	{
		$previous = new \RuntimeException('Scope validation failed');
		$exception = new OAuthScopeException('Missing required scope', 0, $previous);

		$this->assertSame($previous, $exception->getPrevious());
	}
}
