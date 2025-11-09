<?php
declare(strict_types=1);

namespace Nalgoo\Common\Tests\Unit\Application\Exceptions;

use Nalgoo\Common\Application\Exceptions\ApplicationException;
use Nalgoo\Common\Application\Exceptions\AuthorizationException;
use PHPUnit\Framework\TestCase;

final class AuthorizationExceptionTest extends TestCase
{
	public function testConstructor(): void
	{
		$exception = new AuthorizationException('Not authorized');

		$this->assertInstanceOf(AuthorizationException::class, $exception);
		$this->assertInstanceOf(ApplicationException::class, $exception);
		$this->assertSame('Not authorized', $exception->getMessage());
	}

	public function testConstructorWithCode(): void
	{
		$exception = new AuthorizationException('Not authorized', 403);

		$this->assertSame('Not authorized', $exception->getMessage());
		$this->assertSame(403, $exception->getCode());
	}

	public function testConstructorWithPrevious(): void
	{
		$previous = new \RuntimeException('Token invalid');
		$exception = new AuthorizationException('Not authorized', 0, $previous);

		$this->assertSame($previous, $exception->getPrevious());
	}
}
