<?php
declare(strict_types=1);

namespace Nalgoo\Common\Tests\Unit\Domain\Exceptions;

use Nalgoo\Common\Domain\Exceptions\DomainException;
use Nalgoo\Common\Domain\Exceptions\DomainRecordNotFoundException;
use PHPUnit\Framework\TestCase;

final class DomainRecordNotFoundExceptionTest extends TestCase
{
	public function testConstructor(): void
	{
		$exception = new DomainRecordNotFoundException('Record not found');

		$this->assertInstanceOf(DomainRecordNotFoundException::class, $exception);
		$this->assertInstanceOf(DomainException::class, $exception);
		$this->assertSame('Record not found', $exception->getMessage());
	}

	public function testConstructorWithCode(): void
	{
		$exception = new DomainRecordNotFoundException('Record not found', 404);

		$this->assertSame('Record not found', $exception->getMessage());
		$this->assertSame(404, $exception->getCode());
	}

	public function testConstructorWithPrevious(): void
	{
		$previous = new \RuntimeException('Database error');
		$exception = new DomainRecordNotFoundException('Record not found', 0, $previous);

		$this->assertSame($previous, $exception->getPrevious());
	}
}
