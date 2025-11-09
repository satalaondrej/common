<?php
declare(strict_types=1);

namespace Nalgoo\Common\Tests\Unit\Domain\Exceptions;

use Nalgoo\Common\Domain\Exceptions\DomainException;
use Nalgoo\Common\Domain\Exceptions\PropertyValueNotChangedException;
use PHPUnit\Framework\TestCase;

final class PropertyValueNotChangedExceptionTest extends TestCase
{
	public function testConstructor(): void
	{
		$exception = new PropertyValueNotChangedException('Property value not changed');

		$this->assertInstanceOf(PropertyValueNotChangedException::class, $exception);
		$this->assertInstanceOf(DomainException::class, $exception);
		$this->assertSame('Property value not changed', $exception->getMessage());
	}

	public function testConstructorWithCode(): void
	{
		$exception = new PropertyValueNotChangedException('Property value not changed', 400);

		$this->assertSame('Property value not changed', $exception->getMessage());
		$this->assertSame(400, $exception->getCode());
	}

	public function testConstructorWithPrevious(): void
	{
		$previous = new \RuntimeException('Comparison error');
		$exception = new PropertyValueNotChangedException('Property value not changed', 0, $previous);

		$this->assertSame($previous, $exception->getPrevious());
	}
}
