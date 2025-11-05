<?php
declare(strict_types=1);

namespace Nalgoo\Common\Tests\Unit\Domain;

use Nalgoo\Common\Domain\IntegerIdentifier;
use PHPUnit\Framework\TestCase;

final class IntegerIdentifierTest extends TestCase
{
	public function testConstructor(): void
	{
		$id = new IntegerIdentifier(123);

		$this->assertSame(123, $id->toInt());
	}

	public function testFromInt(): void
	{
		$id = IntegerIdentifier::fromInt(456);

		$this->assertSame(456, $id->toInt());
	}

	public function testToString(): void
	{
		$id = new IntegerIdentifier(789);

		$this->assertSame('789', (string) $id);
	}

	public function testJsonSerialize(): void
	{
		$id = new IntegerIdentifier(999);

		$this->assertSame(999, $id->jsonSerialize());
		$this->assertSame('999', json_encode($id));
	}

	public function testSameAsReturnsTrueForEqualIdentifiers(): void
	{
		$id1 = new IntegerIdentifier(100);
		$id2 = new IntegerIdentifier(100);

		$this->assertTrue($id1->sameAs($id2));
		$this->assertTrue($id2->sameAs($id1));
	}

	public function testSameAsReturnsFalseForDifferentValues(): void
	{
		$id1 = new IntegerIdentifier(1);
		$id2 = new IntegerIdentifier(2);

		$this->assertFalse($id1->sameAs($id2));
		$this->assertFalse($id2->sameAs($id1));
	}

	public function testSameAsReturnsFalseForDifferentClasses(): void
	{
		$id1 = new IntegerIdentifier(42);
		$id2 = new class(42) extends IntegerIdentifier {
		};

		$this->assertFalse($id1->sameAs($id2));
		$this->assertFalse($id2->sameAs($id1));
	}

	public function testZeroValue(): void
	{
		$id = new IntegerIdentifier(0);

		$this->assertSame(0, $id->toInt());
		$this->assertSame('0', (string) $id);
	}

	public function testNegativeValue(): void
	{
		$id = IntegerIdentifier::fromInt(-100);

		$this->assertSame(-100, $id->toInt());
		$this->assertSame('-100', (string) $id);
	}

	public function testLargeValue(): void
	{
		$largeInt = PHP_INT_MAX;
		$id = new IntegerIdentifier($largeInt);

		$this->assertSame($largeInt, $id->toInt());
		$this->assertSame((string) $largeInt, (string) $id);
	}
}
