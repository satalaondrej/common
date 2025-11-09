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

	public function testValidYearOnly(): void
	{
		$date = new PartialDate(2024, null, null);

		$this->assertSame(2024, $date->getYear());
		$this->assertNull($date->getMonth());
		$this->assertNull($date->getDay());
	}

	public function testValidYearBoundaries(): void
	{
		$date1 = new PartialDate(1, null, null);
		$this->assertSame(1, $date1->getYear());

		$date2 = new PartialDate(9999, null, null);
		$this->assertSame(9999, $date2->getYear());
	}

	public function testFromDateWithDateTime(): void
	{
		$dateTime = new \DateTime('2024-01-15');
		$date = PartialDate::fromDate($dateTime);

		$this->assertSame(2024, $date->getYear());
		$this->assertSame(1, $date->getMonth());
		$this->assertSame(15, $date->getDay());
	}

	public function testFromDateWithDateTimeImmutable(): void
	{
		$dateTime = new \DateTimeImmutable('2023-06-30');
		$date = PartialDate::fromDate($dateTime);

		$this->assertSame(2023, $date->getYear());
		$this->assertSame(6, $date->getMonth());
		$this->assertSame(30, $date->getDay());
	}

	public function testNegativeYear(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Expected a value greater than 0');

		new PartialDate(-1, null, null);
	}

	public function testYearExactlyZero(): void
	{
		$this->expectException(InvalidArgumentException::class);

		new PartialDate(0, null, null);
	}

	public function testYearExactly10000(): void
	{
		$this->expectException(InvalidArgumentException::class);

		new PartialDate(10000, null, null);
	}

	public function testYearExactly9999Works(): void
	{
		$date = new PartialDate(9999, null, null);

		$this->assertSame(9999, $date->getYear());
	}

	public function testYearExactly1Works(): void
	{
		$date = new PartialDate(1, null, null);

		$this->assertSame(1, $date->getYear());
	}

	public function testMiddleYearValue(): void
	{
		$date = new PartialDate(2000, null, null);

		$this->assertSame(2000, $date->getYear());
		$this->assertNull($date->getMonth());
		$this->assertNull($date->getDay());
	}
}
