<?php
declare(strict_types=1);

namespace Nalgoo\Common\Tests\Unit\Infrastructure\Persistence\Exceptions;

use Nalgoo\Common\Infrastructure\Persistence\Exceptions\ConnectionException;
use Nalgoo\Common\Infrastructure\Persistence\PersistenceException;
use PHPUnit\Framework\TestCase;

final class ConnectionExceptionTest extends TestCase
{
	public function testConstructor(): void
	{
		$exception = new ConnectionException('Connection failed');

		$this->assertInstanceOf(ConnectionException::class, $exception);
		$this->assertInstanceOf(PersistenceException::class, $exception);
		$this->assertSame('Connection failed', $exception->getMessage());
	}

	public function testConstructorWithCode(): void
	{
		$exception = new ConnectionException('Connection failed', 2002);

		$this->assertSame('Connection failed', $exception->getMessage());
		$this->assertSame(2002, $exception->getCode());
	}

	public function testConstructorWithPrevious(): void
	{
		$previous = new \RuntimeException('Network error');
		$exception = new ConnectionException('Connection failed', 0, $previous);

		$this->assertSame($previous, $exception->getPrevious());
	}
}
