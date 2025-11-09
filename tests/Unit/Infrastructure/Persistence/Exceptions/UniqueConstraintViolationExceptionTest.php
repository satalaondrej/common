<?php
declare(strict_types=1);

namespace Nalgoo\Common\Tests\Unit\Infrastructure\Persistence\Exceptions;

use Nalgoo\Common\Infrastructure\Persistence\Exceptions\UniqueConstraintViolationException;
use Nalgoo\Common\Infrastructure\Persistence\PersistenceException;
use PHPUnit\Framework\TestCase;

final class UniqueConstraintViolationExceptionTest extends TestCase
{
	public function testConstructor(): void
	{
		$exception = new UniqueConstraintViolationException('Duplicate entry');

		$this->assertInstanceOf(UniqueConstraintViolationException::class, $exception);
		$this->assertInstanceOf(PersistenceException::class, $exception);
		$this->assertSame('Duplicate entry', $exception->getMessage());
	}

	public function testConstructorWithCode(): void
	{
		$exception = new UniqueConstraintViolationException('Duplicate entry', 1062);

		$this->assertSame('Duplicate entry', $exception->getMessage());
		$this->assertSame(1062, $exception->getCode());
	}

	public function testConstructorWithPrevious(): void
	{
		$previous = new \RuntimeException('Database constraint error');
		$exception = new UniqueConstraintViolationException('Duplicate entry', 0, $previous);

		$this->assertSame($previous, $exception->getPrevious());
	}
}
