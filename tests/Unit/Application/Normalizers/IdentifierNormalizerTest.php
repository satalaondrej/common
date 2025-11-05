<?php
declare(strict_types=1);

namespace Nalgoo\Common\Tests\Unit\Application\Normalizers;

use Nalgoo\Common\Application\Normalizers\IdentifierNormalizer;
use Nalgoo\Common\Domain\IntegerIdentifier;
use Nalgoo\Common\Domain\StringIdentifier;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;

final class IdentifierNormalizerTest extends TestCase
{
	private IdentifierNormalizer $normalizer;

	protected function setUp(): void
	{
		$this->normalizer = new IdentifierNormalizer();
	}

	public function testGetSupportedTypes(): void
	{
		$types = $this->normalizer->getSupportedTypes(null);

		$this->assertArrayHasKey(StringIdentifier::class, $types);
		$this->assertArrayHasKey(IntegerIdentifier::class, $types);
		$this->assertTrue($types[StringIdentifier::class]);
		$this->assertTrue($types[IntegerIdentifier::class]);
	}

	public function testDenormalizeStringIdentifierSubclass(): void
	{
		$subclass = new class('value') extends StringIdentifier {
		};
		$className = get_class($subclass);

		$result = $this->normalizer->denormalize('custom-id', $className);

		$this->assertInstanceOf($className, $result);
		$this->assertSame('custom-id', $result->toString());
	}

	public function testDenormalizeIntegerIdentifierSubclass(): void
	{
		$subclass = new class(123) extends IntegerIdentifier {
		};
		$className = get_class($subclass);

		$result = $this->normalizer->denormalize(999, $className);

		$this->assertInstanceOf($className, $result);
		$this->assertSame(999, $result->toInt());
	}

	public function testDenormalizeThrowsExceptionForUnsupportedType(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->normalizer->denormalize('string', \stdClass::class);
	}

	public function testDenormalizeThrowsExceptionForWrongDataType(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->normalizer->denormalize(123, StringIdentifier::class); // int instead of string
	}

	public function testSupportsDenormalizationStringIdentifierSubclass(): void
	{
		$subclass = new class('value') extends StringIdentifier {
		};
		$className = get_class($subclass);

		$this->assertTrue($this->normalizer->supportsDenormalization('string', $className));
	}

	public function testSupportsDenormalizationIntegerIdentifierSubclass(): void
	{
		$subclass = new class(123) extends IntegerIdentifier {
		};
		$className = get_class($subclass);

		$this->assertTrue($this->normalizer->supportsDenormalization(999, $className));
	}

	public function testSupportsDenormalizationReturnsFalseForWrongDataType(): void
	{
		$this->assertFalse($this->normalizer->supportsDenormalization(123, StringIdentifier::class));
		$this->assertFalse($this->normalizer->supportsDenormalization('string', IntegerIdentifier::class));
	}

	public function testSupportsDenormalizationReturnsFalseForWrongClass(): void
	{
		$this->assertFalse($this->normalizer->supportsDenormalization('string', \stdClass::class));
		$this->assertFalse($this->normalizer->supportsDenormalization(123, \stdClass::class));
	}

	public function testSupportsDenormalizationReturnsFalseForArray(): void
	{
		$this->assertFalse($this->normalizer->supportsDenormalization(['array'], StringIdentifier::class));
		$this->assertFalse($this->normalizer->supportsDenormalization(['array'], IntegerIdentifier::class));
	}

	public function testSupportsDenormalizationReturnsFalseForObject(): void
	{
		$this->assertFalse($this->normalizer->supportsDenormalization(new \stdClass(), StringIdentifier::class));
		$this->assertFalse($this->normalizer->supportsDenormalization(new \stdClass(), IntegerIdentifier::class));
	}

	public function testSupportsDenormalizationReturnsFalseForNull(): void
	{
		$this->assertFalse($this->normalizer->supportsDenormalization(null, StringIdentifier::class));
		$this->assertFalse($this->normalizer->supportsDenormalization(null, IntegerIdentifier::class));
	}

	public function testDenormalizeWithFormat(): void
	{
		$subclass = new class('value') extends StringIdentifier {
		};
		$className = get_class($subclass);

		$result = $this->normalizer->denormalize('test-id', $className, 'json');

		$this->assertInstanceOf($className, $result);
		$this->assertSame('test-id', $result->toString());
	}

	public function testDenormalizeWithContext(): void
	{
		$subclass = new class(123) extends IntegerIdentifier {
		};
		$className = get_class($subclass);

		$result = $this->normalizer->denormalize(456, $className, null, ['groups' => ['api']]);

		$this->assertInstanceOf($className, $result);
		$this->assertSame(456, $result->toInt());
	}

	public function testDenormalizeEmptyString(): void
	{
		$subclass = new class('value') extends StringIdentifier {
		};
		$className = get_class($subclass);

		$result = $this->normalizer->denormalize('', $className);

		$this->assertInstanceOf($className, $result);
		$this->assertSame('', $result->toString());
	}

	public function testDenormalizeZeroInteger(): void
	{
		$subclass = new class(123) extends IntegerIdentifier {
		};
		$className = get_class($subclass);

		$result = $this->normalizer->denormalize(0, $className);

		$this->assertInstanceOf($className, $result);
		$this->assertSame(0, $result->toInt());
	}

	public function testDenormalizeNegativeInteger(): void
	{
		$subclass = new class(123) extends IntegerIdentifier {
		};
		$className = get_class($subclass);

		$result = $this->normalizer->denormalize(-100, $className);

		$this->assertInstanceOf($className, $result);
		$this->assertSame(-100, $result->toInt());
	}
}
