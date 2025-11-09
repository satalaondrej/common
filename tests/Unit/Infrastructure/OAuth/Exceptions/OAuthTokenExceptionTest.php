<?php
declare(strict_types=1);

namespace Nalgoo\Common\Tests\Unit\Infrastructure\OAuth\Exceptions;

use Nalgoo\Common\Infrastructure\OAuth\Exceptions\OAuthException;
use Nalgoo\Common\Infrastructure\OAuth\Exceptions\OAuthTokenException;
use PHPUnit\Framework\TestCase;

final class OAuthTokenExceptionTest extends TestCase
{
	public function testConstructor(): void
	{
		$exception = new OAuthTokenException('Invalid token');

		$this->assertInstanceOf(OAuthTokenException::class, $exception);
		$this->assertInstanceOf(OAuthException::class, $exception);
		$this->assertSame('Invalid token', $exception->getMessage());
	}

	public function testConstructorWithCode(): void
	{
		$exception = new OAuthTokenException('Invalid token', 401);

		$this->assertSame('Invalid token', $exception->getMessage());
		$this->assertSame(401, $exception->getCode());
	}

	public function testConstructorWithPrevious(): void
	{
		$previous = new \RuntimeException('JWT parse error');
		$exception = new OAuthTokenException('Invalid token', 0, $previous);

		$this->assertSame($previous, $exception->getPrevious());
	}
}
