<?php
declare(strict_types=1);

namespace Nalgoo\Common\Tests\Unit\Application\DTO;

use Nalgoo\Common\Application\DTO\NamedValue;
use PHPUnit\Framework\TestCase;

enum TestStringEnum: string
{
	case FOO = 'foo';
	case BAR = 'bar';
	case BAZ = 'baz';
}

enum TestIntEnum: int
{
	case ONE = 1;
	case TWO = 2;
	case THREE = 3;
}

final class NamedValueTest extends TestCase
{
	public function testConstructorWithStringBackedEnum(): void
	{
		$namedValue = new NamedValue(TestStringEnum::FOO, 'test value');

		$this->assertSame(TestStringEnum::FOO, $namedValue->getName());
		$this->assertSame('test value', $namedValue->getValue());
	}

	public function testConstructorWithIntBackedEnum(): void
	{
		$namedValue = new NamedValue(TestIntEnum::TWO, 42);

		$this->assertSame(TestIntEnum::TWO, $namedValue->getName());
		$this->assertSame(42, $namedValue->getValue());
	}

	public function testStringValue(): void
	{
		$namedValue = new NamedValue(TestStringEnum::BAR, 'string value');

		$this->assertIsString($namedValue->getValue());
		$this->assertSame('string value', $namedValue->getValue());
	}

	public function testIntValue(): void
	{
		$namedValue = new NamedValue(TestIntEnum::ONE, 100);

		$this->assertIsInt($namedValue->getValue());
		$this->assertSame(100, $namedValue->getValue());
	}

	public function testFloatValue(): void
	{
		$namedValue = new NamedValue(TestStringEnum::BAZ, 3.14);

		$this->assertIsFloat($namedValue->getValue());
		$this->assertSame(3.14, $namedValue->getValue());
	}

	public function testBoolValue(): void
	{
		$namedValue = new NamedValue(TestIntEnum::THREE, true);

		$this->assertIsBool($namedValue->getValue());
		$this->assertTrue($namedValue->getValue());
	}

	public function testArrayValue(): void
	{
		$array = ['key1' => 'value1', 'key2' => 'value2'];
		$namedValue = new NamedValue(TestStringEnum::FOO, $array);

		$this->assertIsArray($namedValue->getValue());
		$this->assertSame($array, $namedValue->getValue());
	}

	public function testNullValue(): void
	{
		$namedValue = new NamedValue(TestIntEnum::ONE, null);

		$this->assertNull($namedValue->getValue());
	}

	public function testObjectValue(): void
	{
		$object = new \stdClass();
		$object->property = 'test';
		$namedValue = new NamedValue(TestStringEnum::BAR, $object);

		$this->assertIsObject($namedValue->getValue());
		$this->assertSame($object, $namedValue->getValue());
	}

	public function testMultipleInstances(): void
	{
		$nv1 = new NamedValue(TestStringEnum::FOO, 'value1');
		$nv2 = new NamedValue(TestStringEnum::BAR, 'value2');
		$nv3 = new NamedValue(TestIntEnum::ONE, 123);

		$this->assertSame(TestStringEnum::FOO, $nv1->getName());
		$this->assertSame('value1', $nv1->getValue());

		$this->assertSame(TestStringEnum::BAR, $nv2->getName());
		$this->assertSame('value2', $nv2->getValue());

		$this->assertSame(TestIntEnum::ONE, $nv3->getName());
		$this->assertSame(123, $nv3->getValue());
	}

	public function testGetNameReturnsBackedEnum(): void
	{
		$namedValue = new NamedValue(TestStringEnum::BAZ, 'test');

		$name = $namedValue->getName();
		$this->assertInstanceOf(\BackedEnum::class, $name);
		$this->assertSame('baz', $name->value);
	}

	public function testIntBackedEnumValue(): void
	{
		$namedValue = new NamedValue(TestIntEnum::TWO, 'test');

		$name = $namedValue->getName();
		$this->assertInstanceOf(\BackedEnum::class, $name);
		$this->assertSame(2, $name->value);
	}
}
