<?php
declare(strict_types=1);

namespace Nalgoo\Common\Tests\Unit\Application\DTO;

use Nalgoo\Common\Application\DTO\PartialDate;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\InvalidArgumentException;

final class PartialDateTest extends TestCase
{
	public function testYearTooSmallThrowsException(): void
	{
		$this->expectException(InvalidArgumentException::class);

		new PartialDate(0, null, null);
	}

	public function testYearTooLargeThrowsException(): void
	{
		$this->expectException(InvalidArgumentException::class);

		new PartialDate(10000, null, null);
	}

	public function testNullValues(): void
	{
		$date = new PartialDate(null, null, null);

		$this->assertNull($date->getYear());
		$this->assertNull($date->getMonth());
		$this->assertNull($date->getDay());
	}

	public function testGettersReturnCorrectValues(): void
	{
		$date = new PartialDate(2024, null, null);

		$this->assertSame(2024, $date->getYear());
		$this->assertNull($date->getMonth());
		$this->assertNull($date->getDay());
	}
}
