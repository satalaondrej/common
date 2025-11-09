<?php
declare(strict_types=1);

namespace Nalgoo\Common\Tests\Unit\Infrastructure\Persistence;

use Nalgoo\Common\Infrastructure\Persistence\Exceptions\ConnectionException;
use Nalgoo\Common\Infrastructure\Persistence\Exceptions\UniqueConstraintViolationException;
use Nalgoo\Common\Infrastructure\Persistence\PersistenceException;
use PHPUnit\Framework\TestCase;

final class PersistenceExceptionTest extends TestCase
{
	public function testConstructor(): void
	{
		$exception = new PersistenceException('Persistence error');

		$this->assertInstanceOf(PersistenceException::class, $exception);
		$this->assertSame('Persistence error', $exception->getMessage());
	}

	public function testConstructorWithCode(): void
	{
		$exception = new PersistenceException('Persistence error', 500);

		$this->assertSame('Persistence error', $exception->getMessage());
		$this->assertSame(500, $exception->getCode());
	}

	public function testConstructorWithPrevious(): void
	{
		$previous = new \RuntimeException('Database error');
		$exception = new PersistenceException('Persistence error', 0, $previous);

		$this->assertSame($previous, $exception->getPrevious());
	}

	public function testFromGenericException(): void
	{
		// Test that PersistenceException::from() correctly wraps generic exceptions
		// The from() method uses a match expression to convert specific Doctrine
		// exceptions to their corresponding typed exceptions, but generic exceptions
		// are wrapped as PersistenceException
		$previous = new \RuntimeException('Generic database error', 999);

		$exception = PersistenceException::from($previous);

		$this->assertInstanceOf(PersistenceException::class, $exception);
		$this->assertNotInstanceOf(ConnectionException::class, $exception);
		$this->assertNotInstanceOf(UniqueConstraintViolationException::class, $exception);
		$this->assertSame('Generic database error', $exception->getMessage());
		$this->assertSame(999, $exception->getCode());
		$this->assertSame($previous, $exception->getPrevious());
	}
}
