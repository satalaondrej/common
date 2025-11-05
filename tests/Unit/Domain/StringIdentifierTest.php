<?php
declare(strict_types=1);

namespace Nalgoo\Common\Tests\Unit\Domain;

use Nalgoo\Common\Domain\StringIdentifier;
use PHPUnit\Framework\TestCase;

final class StringIdentifierTest extends TestCase
{
	public function testConstructor(): void
	{
		$id = new StringIdentifier('test-id-123');

		$this->assertSame('test-id-123', $id->toString());
	}

	public function testFromString(): void
	{
		$id = StringIdentifier::fromString('uuid-456');

		$this->assertSame('uuid-456', $id->toString());
	}

	public function testToString(): void
	{
		$id = new StringIdentifier('string-id');

		$this->assertSame('string-id', (string) $id);
	}

	public function testJsonSerialize(): void
	{
		$id = new StringIdentifier('json-id');

		$this->assertSame('json-id', $id->jsonSerialize());
		$this->assertSame('"json-id"', json_encode($id));
	}

	public function testSameAsReturnsTrueForEqualIdentifiers(): void
	{
		$id1 = new StringIdentifier('same-id');
		$id2 = new StringIdentifier('same-id');

		$this->assertTrue($id1->sameAs($id2));
		$this->assertTrue($id2->sameAs($id1));
	}

	public function testSameAsReturnsFalseForDifferentValues(): void
	{
		$id1 = new StringIdentifier('id-1');
		$id2 = new StringIdentifier('id-2');

		$this->assertFalse($id1->sameAs($id2));
		$this->assertFalse($id2->sameAs($id1));
	}

	public function testSameAsReturnsFalseForDifferentClasses(): void
	{
		$id1 = new StringIdentifier('same-value');
		$id2 = new class('same-value') extends StringIdentifier {
		};

		$this->assertFalse($id1->sameAs($id2));
		$this->assertFalse($id2->sameAs($id1));
	}

	public function testEmptyString(): void
	{
		$id = new StringIdentifier('');

		$this->assertSame('', $id->toString());
		$this->assertSame('', (string) $id);
	}

	public function testUuidString(): void
	{
		$uuid = '550e8400-e29b-41d4-a716-446655440000';
		$id = StringIdentifier::fromString($uuid);

		$this->assertSame($uuid, $id->toString());
	}

	public function testSpecialCharacters(): void
	{
		$special = 'id-with-special-chars_@#$%';
		$id = new StringIdentifier($special);

		$this->assertSame($special, $id->toString());
	}
}
