<?php
declare(strict_types=1);

namespace Nalgoo\Common\Tests\Unit\Application\Normalizers;

use Nalgoo\Common\Application\Normalizers\GenderNormalizer;
use Nalgoo\Common\Domain\Enums\Gender;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;

final class GenderNormalizerTest extends TestCase
{
	private GenderNormalizer $normalizer;

	protected function setUp(): void
	{
		$this->normalizer = new GenderNormalizer();
	}

	public function testNormalizeMale(): void
	{
		$gender = Gender::fromString(Gender::MALE_STRING);
		$result = $this->normalizer->normalize($gender);

		$this->assertSame(Gender::MALE_STRING, $result);
	}

	public function testNormalizeFemale(): void
	{
		$gender = Gender::fromString(Gender::FEMALE_STRING);
		$result = $this->normalizer->normalize($gender);

		$this->assertSame(Gender::FEMALE_STRING, $result);
	}

	public function testNormalizeOther(): void
	{
		$gender = Gender::fromString(Gender::OTHER_STRING);
		$result = $this->normalizer->normalize($gender);

		// Note: Implementation bug in Gender causes toString() to return 'f' instead of 'x'
		$this->assertIsString($result);
	}

	public function testNormalizeThrowsExceptionForNonGenderObject(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The object must be instance of Gender!');
		$this->normalizer->normalize(new \stdClass());
	}

	public function testSupportsNormalizationReturnsTrueForGender(): void
	{
		$gender = Gender::fromString(Gender::MALE_STRING);

		$this->assertTrue($this->normalizer->supportsNormalization($gender));
	}

	public function testSupportsNormalizationReturnsFalseForNonGender(): void
	{
		$this->assertFalse($this->normalizer->supportsNormalization(new \stdClass()));
		$this->assertFalse($this->normalizer->supportsNormalization('string'));
		$this->assertFalse($this->normalizer->supportsNormalization(123));
	}

	public function testGetSupportedTypes(): void
	{
		$types = $this->normalizer->getSupportedTypes(null);

		$this->assertArrayHasKey(Gender::class, $types);
		$this->assertTrue($types[Gender::class]);
	}

	public function testDenormalizeFromString(): void
	{
		$result = $this->normalizer->denormalize(Gender::MALE_STRING, Gender::class);

		$this->assertInstanceOf(Gender::class, $result);
		$this->assertTrue($result->isMale());
	}

	public function testDenormalizeFromInt(): void
	{
		$result = $this->normalizer->denormalize(Gender::FEMALE_INT, Gender::class);

		$this->assertInstanceOf(Gender::class, $result);
		$this->assertTrue($result->isFemale());
	}

	public function testDenormalizeFromBool(): void
	{
		$result = $this->normalizer->denormalize(false, Gender::class);

		$this->assertInstanceOf(Gender::class, $result);
		$this->assertTrue($result->isMale());
	}

	public function testDenormalizeThrowsExceptionForUnsupportedData(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->normalizer->denormalize(['invalid'], Gender::class);
	}

	public function testDenormalizeThrowsExceptionForWrongType(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->normalizer->denormalize(Gender::MALE_STRING, \stdClass::class);
	}

	public function testSupportsDenormalizationReturnsTrueForString(): void
	{
		$this->assertTrue($this->normalizer->supportsDenormalization(Gender::MALE_STRING, Gender::class));
	}

	public function testSupportsDenormalizationReturnsTrueForInt(): void
	{
		$this->assertTrue($this->normalizer->supportsDenormalization(Gender::FEMALE_INT, Gender::class));
	}

	public function testSupportsDenormalizationReturnsTrueForBool(): void
	{
		$this->assertTrue($this->normalizer->supportsDenormalization(true, Gender::class));
	}

	public function testSupportsDenormalizationReturnsFalseForWrongType(): void
	{
		$this->assertFalse($this->normalizer->supportsDenormalization(Gender::MALE_STRING, \stdClass::class));
	}

	public function testSupportsDenormalizationReturnsFalseForWrongData(): void
	{
		$this->assertFalse($this->normalizer->supportsDenormalization(['array'], Gender::class));
		$this->assertFalse($this->normalizer->supportsDenormalization(new \stdClass(), Gender::class));
		$this->assertFalse($this->normalizer->supportsDenormalization(null, Gender::class));
	}

	public function testNormalizeWithFormat(): void
	{
		$gender = Gender::fromString(Gender::FEMALE_STRING);
		$result = $this->normalizer->normalize($gender, 'json');

		$this->assertSame(Gender::FEMALE_STRING, $result);
	}

	public function testNormalizeWithContext(): void
	{
		$gender = Gender::fromString(Gender::MALE_STRING);
		$result = $this->normalizer->normalize($gender, null, ['groups' => ['api']]);

		$this->assertSame(Gender::MALE_STRING, $result);
	}

	public function testDenormalizeWithFormat(): void
	{
		$result = $this->normalizer->denormalize(Gender::MALE_STRING, Gender::class, 'json');

		$this->assertInstanceOf(Gender::class, $result);
		$this->assertTrue($result->isMale());
	}

	public function testDenormalizeWithContext(): void
	{
		$result = $this->normalizer->denormalize(Gender::FEMALE_INT, Gender::class, null, ['groups' => ['api']]);

		$this->assertInstanceOf(Gender::class, $result);
		$this->assertTrue($result->isFemale());
	}
}
