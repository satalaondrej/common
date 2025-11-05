<?php
declare(strict_types=1);

namespace Nalgoo\Common\Tests\Unit\Application\Exceptions;

use Nalgoo\Common\Application\Exceptions\ApplicationException;
use Nalgoo\Common\Application\Exceptions\DeserializeException;
use PHPUnit\Framework\TestCase;

final class DeserializeExceptionTest extends TestCase
{
	public function testConstructor(): void
	{
		$exception = new DeserializeException('Deserialization failed');

		$this->assertInstanceOf(DeserializeException::class, $exception);
		$this->assertInstanceOf(ApplicationException::class, $exception);
		$this->assertSame('Deserialization failed', $exception->getMessage());
	}

	public function testConstructorWithCode(): void
	{
		$exception = new DeserializeException('Deserialization failed', 500);

		$this->assertSame('Deserialization failed', $exception->getMessage());
		$this->assertSame(500, $exception->getCode());
	}

	public function testConstructorWithPrevious(): void
	{
		$previous = new \RuntimeException('JSON parse error');
		$exception = new DeserializeException('Deserialization failed: JSON parse error', 0, $previous);

		$this->assertSame($previous, $exception->getPrevious());
	}

	public function testThrowAndCatch(): void
	{
		try {
			throw new DeserializeException('Test exception');
		} catch (DeserializeException $e) {
			$this->assertSame('Test exception', $e->getMessage());
		}
	}
}
