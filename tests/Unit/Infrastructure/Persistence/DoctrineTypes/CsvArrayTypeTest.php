<?php
declare(strict_types=1);

namespace Nalgoo\Common\Tests\Unit\Infrastructure\Persistence\DoctrineTypes;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Nalgoo\Common\Infrastructure\Persistence\DoctrineTypes\CsvArrayType;
use PHPUnit\Framework\TestCase;

final class CsvArrayTypeTest extends TestCase
{
	private CsvArrayType $type;
	private AbstractPlatform $platform;

	protected function setUp(): void
	{
		$this->type = new CsvArrayType();
		$this->platform = $this->createMock(AbstractPlatform::class);
	}

	public function testGetName(): void
	{
		$this->assertSame('csv_array', $this->type->getName());
	}

	public function testConvertToDatabaseValueWithSimpleArray(): void
	{
		$value = ['apple', 'banana', 'cherry'];
		$result = $this->type->convertToDatabaseValue($value, $this->platform);

		$this->assertIsString($result);
		$this->assertStringContainsString('apple', $result);
		$this->assertStringContainsString('banana', $result);
		$this->assertStringContainsString('cherry', $result);
	}

	public function testConvertToDatabaseValueWithEmptyArray(): void
	{
		$result = $this->type->convertToDatabaseValue([], $this->platform);

		$this->assertNull($result);
	}

	public function testConvertToDatabaseValueWithNull(): void
	{
		$result = $this->type->convertToDatabaseValue(null, $this->platform);

		$this->assertNull($result);
	}

	public function testConvertToPHPValueWithNull(): void
	{
		$result = $this->type->convertToPHPValue(null, $this->platform);

		$this->assertIsArray($result);
		$this->assertEmpty($result);
	}

	public function testConvertToPHPValueWithCsvString(): void
	{
		$csv = "apple,banana,cherry\n";
		$result = $this->type->convertToPHPValue($csv, $this->platform);

		$this->assertIsArray($result);
		$this->assertCount(3, $result);
		$this->assertSame(['apple', 'banana', 'cherry'], $result);
	}

	public function testRoundTripConversion(): void
	{
		$original = ['value1', 'value2', 'value3'];

		$database = $this->type->convertToDatabaseValue($original, $this->platform);
		$this->assertIsString($database);

		$php = $this->type->convertToPHPValue($database, $this->platform);
		$this->assertSame($original, $php);
	}

	public function testConvertToPHPValueWithQuotedValues(): void
	{
		$csv = '"value with spaces","another value","third"';
		$result = $this->type->convertToPHPValue($csv, $this->platform);

		$this->assertIsArray($result);
		$this->assertCount(3, $result);
		$this->assertSame('value with spaces', $result[0]);
		$this->assertSame('another value', $result[1]);
		$this->assertSame('third', $result[2]);
	}

	public function testConvertToDatabaseValueWithSpecialCharacters(): void
	{
		$value = ['value,with,commas', 'value"with"quotes', 'normal'];
		$result = $this->type->convertToDatabaseValue($value, $this->platform);

		$this->assertIsString($result);

		// Round trip to verify integrity
		$php = $this->type->convertToPHPValue($result, $this->platform);
		$this->assertSame($value, $php);
	}

	public function testConvertToPHPValueWithSingleValue(): void
	{
		$csv = "single\n";
		$result = $this->type->convertToPHPValue($csv, $this->platform);

		$this->assertIsArray($result);
		$this->assertCount(1, $result);
		$this->assertSame(['single'], $result);
	}

	public function testConvertToPHPValueReturnsSequentialArray(): void
	{
		$csv = "a,b,c\n";
		$result = $this->type->convertToPHPValue($csv, $this->platform);

		$this->assertSame([0, 1, 2], array_keys($result));
	}

	public function testConvertToDatabaseValueWithNumericStrings(): void
	{
		$value = ['123', '456', '789'];
		$result = $this->type->convertToDatabaseValue($value, $this->platform);

		$this->assertIsString($result);

		$php = $this->type->convertToPHPValue($result, $this->platform);
		$this->assertSame($value, $php);
	}

	public function testConvertToPHPValueWithEmptyString(): void
	{
		$result = $this->type->convertToPHPValue('', $this->platform);

		$this->assertIsArray($result);
		// Empty string returns empty array
		$this->assertEmpty($result);
	}
}
