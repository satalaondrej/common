<?php
declare(strict_types=1);

namespace Nalgoo\Common\Tests\Unit\Infrastructure\OAuth\Exceptions;

use Nalgoo\Common\Infrastructure\OAuth\Exceptions\OAuthAudienceException;
use Nalgoo\Common\Infrastructure\OAuth\Exceptions\OAuthException;
use PHPUnit\Framework\TestCase;

final class OAuthAudienceExceptionTest extends TestCase
{
	public function testConstructor(): void
	{
		$exception = new OAuthAudienceException('Invalid audience');

		$this->assertInstanceOf(OAuthAudienceException::class, $exception);
		$this->assertInstanceOf(OAuthException::class, $exception);
		$this->assertSame('Invalid audience', $exception->getMessage());
	}

	public function testConstructorWithCode(): void
	{
		$exception = new OAuthAudienceException('Invalid audience', 403);

		$this->assertSame('Invalid audience', $exception->getMessage());
		$this->assertSame(403, $exception->getCode());
	}

	public function testConstructorWithPrevious(): void
	{
		$previous = new \RuntimeException('Audience validation failed');
		$exception = new OAuthAudienceException('Invalid audience', 0, $previous);

		$this->assertSame($previous, $exception->getPrevious());
	}
}
