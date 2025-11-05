<?php
declare(strict_types=1);

namespace Nalgoo\Common\Tests\Unit\Domain\Exceptions;

use Nalgoo\Common\Domain\Exceptions\DomainException;
use Nalgoo\Common\Domain\Exceptions\DomainLogicException;
use PHPUnit\Framework\TestCase;

final class DomainLogicExceptionTest extends TestCase
{
	public function testConstructor(): void
	{
		$exception = new DomainLogicException('Test message');

		$this->assertInstanceOf(DomainLogicException::class, $exception);
		$this->assertInstanceOf(DomainException::class, $exception);
		$this->assertSame('Test message', $exception->getMessage());
	}

	public function testConstructorWithCode(): void
	{
		$exception = new DomainLogicException('Test message', 123);

		$this->assertSame('Test message', $exception->getMessage());
		$this->assertSame(123, $exception->getCode());
	}

	public function testConstructorWithPrevious(): void
	{
		$previous = new \RuntimeException('Previous exception');
		$exception = new DomainLogicException('Test message', 0, $previous);

		$this->assertSame($previous, $exception->getPrevious());
	}
}
