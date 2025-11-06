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

	public function testConvertToDatabaseValueWithFalse(): void
	{
		$result = $this->type->convertToDatabaseValue(false, $this->platform);

		$this->assertNull($result);
	}

	public function testConvertToDatabaseValueWith0(): void
	{
		$result = $this->type->convertToDatabaseValue(0, $this->platform);

		$this->assertNull($result);
	}

	public function testConvertToPHPValueWithFalse(): void
	{
		$result = $this->type->convertToPHPValue(false, $this->platform);

		$this->assertIsArray($result);
		$this->assertEmpty($result);
	}

	public function testConvertToPHPValueWithResource(): void
	{
		$resource = fopen('php://memory', 'r+');
		$this->assertIsResource($resource);
		fwrite($resource, 'test1,test2,test3');
		rewind($resource);

		$result = $this->type->convertToPHPValue($resource, $this->platform);

		fclose($resource);

		$this->assertIsArray($result);
		$this->assertCount(3, $result);
		$this->assertSame(['test1', 'test2', 'test3'], $result);
	}

	public function testConvertToDatabaseValueWithSingleElement(): void
	{
		$value = ['single'];
		$result = $this->type->convertToDatabaseValue($value, $this->platform);

		$this->assertIsString($result);
		$this->assertStringContainsString('single', $result);
	}

	public function testConvertToDatabaseValueWithEmptyStringsInArray(): void
	{
		$value = ['', '', ''];
		$result = $this->type->convertToDatabaseValue($value, $this->platform);

		$this->assertIsString($result);

		// Round trip
		$php = $this->type->convertToPHPValue($result, $this->platform);
		$this->assertSame($value, $php);
	}

	public function testConvertToPHPValueWithNewlines(): void
	{
		$csv = "value1,value2,value3\n";
		$result = $this->type->convertToPHPValue($csv, $this->platform);

		$this->assertIsArray($result);
		$this->assertCount(3, $result);
	}

	public function testConvertToPHPValueWithCarriageReturn(): void
	{
		$csv = "value1,value2,value3\r\n";
		$result = $this->type->convertToPHPValue($csv, $this->platform);

		$this->assertIsArray($result);
		$this->assertCount(3, $result);
	}

	public function testConvertToDatabaseValueWithUnicodeCharacters(): void
	{
		$value = ['Привет', 'мир', '世界'];
		$result = $this->type->convertToDatabaseValue($value, $this->platform);

		$this->assertIsString($result);

		// Round trip
		$php = $this->type->convertToPHPValue($result, $this->platform);
		$this->assertSame($value, $php);
	}

	public function testConvertToPHPValueWithQuotedCommas(): void
	{
		$csv = '"a,b","c,d","e,f"';
		$result = $this->type->convertToPHPValue($csv, $this->platform);

		$this->assertIsArray($result);
		$this->assertCount(3, $result);
		$this->assertSame('a,b', $result[0]);
		$this->assertSame('c,d', $result[1]);
		$this->assertSame('e,f', $result[2]);
	}

	public function testRoundTripWithMixedContent(): void
	{
		$value = ['normal', 'with,comma', 'with"quote', 'with spaces', '123', ''];

		$database = $this->type->convertToDatabaseValue($value, $this->platform);
		$this->assertIsString($database);

		$php = $this->type->convertToPHPValue($database, $this->platform);
		$this->assertSame($value, $php);
	}

	/**
	 * Failure Scenario Tests.
	 *
	 * The CsvArrayType now throws explicit exceptions instead of silently failing.
	 * This ensures data integrity by preventing:
	 * - Silent NULL saves when encoding fails
	 * - Silent empty array returns when decoding fails
	 *
	 * Failure paths covered by the implementation:
	 * 1. fopen() fails → throws PersistenceException
	 * 2. fputcsv() fails → throws PersistenceException
	 * 3. fgets() fails → throws PersistenceException
	 * 4. fwrite() fails → throws PersistenceException
	 * 5. fgetcsv() returns false → throws PersistenceException
	 * 6. stream_get_contents() fails → throws PersistenceException
	 *
	 * Note: These scenarios are extremely difficult to test in unit tests as
	 * php://memory operations rarely fail. The exception throwing code is present
	 * to handle edge cases like memory exhaustion or system resource limits.
	 */

	/**
	 * Test that empty values return empty array rather than throwing exceptions.
	 * This is intentional behavior for valid edge cases.
	 */
	public function testEdgeCasesReturnEmptyArrayNotException(): void
	{
		// Null value
		$result1 = $this->type->convertToPHPValue(null, $this->platform);
		$this->assertSame([], $result1);

		// Empty string
		$result2 = $this->type->convertToPHPValue('', $this->platform);
		$this->assertSame([], $result2);

		// False value
		$result3 = $this->type->convertToPHPValue(false, $this->platform);
		$this->assertSame([], $result3);
	}

	/**
	 * Test that empty or falsy values in convertToDatabaseValue return null.
	 * This is intentional behavior for valid edge cases.
	 */
	public function testEmptyValuesToDatabaseReturnNull(): void
	{
		$this->assertNull($this->type->convertToDatabaseValue([], $this->platform));
		$this->assertNull($this->type->convertToDatabaseValue(null, $this->platform));
		$this->assertNull($this->type->convertToDatabaseValue(false, $this->platform));
		$this->assertNull($this->type->convertToDatabaseValue(0, $this->platform));
	}
}
