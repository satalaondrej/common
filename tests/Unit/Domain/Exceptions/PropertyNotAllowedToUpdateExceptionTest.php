<?php
declare(strict_types=1);

namespace Nalgoo\Common\Tests\Unit\Domain\Exceptions;

use Nalgoo\Common\Domain\Exceptions\DomainException;
use Nalgoo\Common\Domain\Exceptions\PropertyNotAllowedToUpdateException;
use PHPUnit\Framework\TestCase;

final class PropertyNotAllowedToUpdateExceptionTest extends TestCase
{
	public function testConstructor(): void
	{
		$exception = new PropertyNotAllowedToUpdateException('Property update not allowed');

		$this->assertInstanceOf(PropertyNotAllowedToUpdateException::class, $exception);
		$this->assertInstanceOf(DomainException::class, $exception);
		$this->assertSame('Property update not allowed', $exception->getMessage());
	}

	public function testConstructorWithCode(): void
	{
		$exception = new PropertyNotAllowedToUpdateException('Property update not allowed', 403);

		$this->assertSame('Property update not allowed', $exception->getMessage());
		$this->assertSame(403, $exception->getCode());
	}

	public function testConstructorWithPrevious(): void
	{
		$previous = new \RuntimeException('Validation error');
		$exception = new PropertyNotAllowedToUpdateException('Property update not allowed', 0, $previous);

		$this->assertSame($previous, $exception->getPrevious());
	}
}
