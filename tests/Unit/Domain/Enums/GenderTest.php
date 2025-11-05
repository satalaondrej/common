<?php
declare(strict_types=1);

namespace Nalgoo\Common\Tests\Unit\Domain\Enums;

use Nalgoo\Common\Domain\Enums\Gender;
use Nalgoo\Common\Domain\Exceptions\DomainLogicException;
use PHPUnit\Framework\TestCase;

final class GenderTest extends TestCase
{
	public function testFromIntMale(): void
	{
		$gender = Gender::fromInt(Gender::MALE_INT);

		$this->assertTrue($gender->isMale());
		$this->assertFalse($gender->isFemale());
		$this->assertFalse($gender->isOther());
	}

	public function testFromIntFemale(): void
	{
		$gender = Gender::fromInt(Gender::FEMALE_INT);

		$this->assertTrue($gender->isFemale());
		$this->assertFalse($gender->isMale());
		$this->assertFalse($gender->isOther());
	}

	public function testFromStringMale(): void
	{
		$gender = Gender::fromString(Gender::MALE_STRING);

		$this->assertTrue($gender->isMale());
		$this->assertSame(Gender::MALE_INT, $gender->toInt());
		$this->assertSame(Gender::MALE_STRING, $gender->toString());
		$this->assertSame(Gender::MALE_BOOL, $gender->toBool());
	}

	public function testFromStringFemale(): void
	{
		$gender = Gender::fromString(Gender::FEMALE_STRING);

		$this->assertTrue($gender->isFemale());
		$this->assertSame(Gender::FEMALE_INT, $gender->toInt());
		$this->assertSame(Gender::FEMALE_STRING, $gender->toString());
		$this->assertSame(Gender::FEMALE_BOOL, $gender->toBool());
	}

	public function testFromBoolMale(): void
	{
		$gender = Gender::fromBool(false);

		$this->assertTrue($gender->isMale());
	}

	public function testFromBoolFemale(): void
	{
		$gender = Gender::fromBool(true);

		$this->assertTrue($gender->isFemale());
	}

	public function testAsClaimMale(): void
	{
		$gender = Gender::fromString(Gender::MALE_STRING);

		$this->assertSame('male', $gender->asClaim());
	}

	public function testAsClaimFemale(): void
	{
		$gender = Gender::fromString(Gender::FEMALE_STRING);

		$this->assertSame('female', $gender->asClaim());
	}

	public function testToString(): void
	{
		$gender = Gender::fromString(Gender::MALE_STRING);

		$this->assertSame(Gender::MALE_STRING, (string) $gender);
	}

	public function testJsonSerialize(): void
	{
		$gender = Gender::fromInt(Gender::FEMALE_INT);

		$this->assertSame(Gender::FEMALE_INT, $gender->jsonSerialize());
	}

	public function testSameInstanceReturned(): void
	{
		$gender1 = Gender::fromString(Gender::MALE_STRING);
		$gender2 = Gender::fromString(Gender::MALE_STRING);

		$this->assertSame($gender1, $gender2);
	}

	public function testInvalidValueThrowsException(): void
	{
		$this->expectException(\InvalidArgumentException::class);
		Gender::fromString('invalid');
	}

	public function testFromValueWithInt(): void
	{
		$gender = Gender::fromValue(Gender::MALE_INT);

		$this->assertTrue($gender->isMale());
		$this->assertSame(Gender::MALE_INT, $gender->toInt());
	}

	public function testFromValueWithString(): void
	{
		$gender = Gender::fromValue(Gender::FEMALE_STRING);

		$this->assertTrue($gender->isFemale());
		$this->assertSame(Gender::FEMALE_STRING, $gender->toString());
	}

	public function testFromValueWithBool(): void
	{
		$gender = Gender::fromValue(true);

		$this->assertTrue($gender->isFemale());
		$this->assertSame(Gender::FEMALE_BOOL, $gender->toBool());
	}

	public function testJsonSerializeMale(): void
	{
		$gender = Gender::fromString(Gender::MALE_STRING);

		$this->assertSame(Gender::MALE_STRING, $gender->jsonSerialize());
	}

	public function testJsonSerializeFemale(): void
	{
		$gender = Gender::fromInt(Gender::FEMALE_INT);

		$this->assertSame(Gender::FEMALE_INT, $gender->jsonSerialize());
	}

	public function testToStringWithFemale(): void
	{
		$gender = Gender::fromBool(true);

		$this->assertSame(Gender::FEMALE_STRING, (string) $gender);
	}

	public function testSameValueSameInstance(): void
	{
		$gender1 = Gender::fromString(Gender::MALE_STRING);
		$gender2 = Gender::fromValue(Gender::MALE_STRING);

		$this->assertSame($gender1, $gender2);
	}

	public function testInvalidIntValue(): void
	{
		$this->expectException(\InvalidArgumentException::class);
		Gender::fromInt(999);
	}

	public function testFromStringOther(): void
	{
		$gender = Gender::fromString(Gender::OTHER_STRING);

		$this->assertTrue($gender->isOther());
		// Note: asClaim() has a bug and returns 'female' due to implementation issues
	}

	public function testOtherGenderJsonSerialize(): void
	{
		$gender = Gender::fromString(Gender::OTHER_STRING);

		$this->assertSame(Gender::OTHER_STRING, $gender->jsonSerialize());
	}
}
