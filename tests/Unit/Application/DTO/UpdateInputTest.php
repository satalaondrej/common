<?php
declare(strict_types=1);

namespace Nalgoo\Common\Tests\Unit\Application\DTO;

use Nalgoo\Common\Application\DTO\NamedValue;
use Nalgoo\Common\Application\DTO\UpdateInput;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\InvalidArgumentException;

enum TestProperty: string
{
	case NAME = 'name';
	case EMAIL = 'email';
	case AGE = 'age';
}

class TestUpdateInput extends UpdateInput
{
	public string $name = '';
	public string $email = '';
	public int $age = 0;

	public function setName(string $name): self
	{
		return $this->setProperty(TestProperty::NAME, $name);
	}

	public function setEmail(string $email): self
	{
		return $this->setProperty(TestProperty::EMAIL, $email);
	}

	public function setAge(int $age): self
	{
		return $this->setProperty(TestProperty::AGE, $age);
	}

	public function addManualUpdate(\BackedEnum $property): self
	{
		return $this->addUpdatedProperty($property);
	}
}

final class UpdateInputTest extends TestCase
{
	public function testSetPropertyUpdatesValue(): void
	{
		$input = new TestUpdateInput();
		$input->setName('John Doe');

		$this->assertSame('John Doe', $input->name);
	}

	public function testSetPropertyTracksUpdate(): void
	{
		$input = new TestUpdateInput();
		$input->setName('John Doe');

		$updated = $input->getUpdatedProperties();

		$this->assertCount(1, $updated);
		$this->assertInstanceOf(NamedValue::class, $updated[0]);
		$this->assertSame(TestProperty::NAME, $updated[0]->getName());
		$this->assertSame('John Doe', $updated[0]->getValue());
	}

	public function testSetMultipleProperties(): void
	{
		$input = new TestUpdateInput();
		$input->setName('John Doe');
		$input->setEmail('john@example.com');
		$input->setAge(30);

		$updated = $input->getUpdatedProperties();

		$this->assertCount(3, $updated);
		$this->assertSame(TestProperty::NAME, $updated[0]->getName());
		$this->assertSame('John Doe', $updated[0]->getValue());
		$this->assertSame(TestProperty::EMAIL, $updated[1]->getName());
		$this->assertSame('john@example.com', $updated[1]->getValue());
		$this->assertSame(TestProperty::AGE, $updated[2]->getName());
		$this->assertSame(30, $updated[2]->getValue());
	}

	public function testSetPropertyFluentInterface(): void
	{
		$input = new TestUpdateInput();
		$result = $input->setName('John')->setEmail('john@example.com')->setAge(25);

		$this->assertSame($input, $result);
		$this->assertSame('John', $input->name);
		$this->assertSame('john@example.com', $input->email);
		$this->assertSame(25, $input->age);
	}

	public function testSetPropertyMultipleTimesTracksOnce(): void
	{
		$input = new TestUpdateInput();
		$input->setName('John');
		$input->setName('Jane');

		$updated = $input->getUpdatedProperties();

		$this->assertCount(1, $updated);
		$this->assertSame(TestProperty::NAME, $updated[0]->getName());
		$this->assertSame('Jane', $updated[0]->getValue());
	}

	public function testAddUpdatedPropertyManually(): void
	{
		$input = new TestUpdateInput();
		$input->name = 'Direct Assignment';
		$input->addManualUpdate(TestProperty::NAME);

		$updated = $input->getUpdatedProperties();

		$this->assertCount(1, $updated);
		$this->assertSame(TestProperty::NAME, $updated[0]->getName());
		$this->assertSame('Direct Assignment', $updated[0]->getValue());
	}

	public function testAddUpdatedPropertyDoesNotDuplicate(): void
	{
		$input = new TestUpdateInput();
		$input->name = 'Test';
		$input->addManualUpdate(TestProperty::NAME);
		$input->addManualUpdate(TestProperty::NAME);

		$updated = $input->getUpdatedProperties();

		$this->assertCount(1, $updated);
	}

	public function testGetUpdatedPropertiesReturnsEmptyArray(): void
	{
		$input = new TestUpdateInput();

		$updated = $input->getUpdatedProperties();

		$this->assertIsArray($updated);
		$this->assertEmpty($updated);
	}

	public function testGetUpdatedPropertiesReturnsNamedValueArray(): void
	{
		$input = new TestUpdateInput();
		$input->setName('Test');

		$updated = $input->getUpdatedProperties();

		$this->assertContainsOnlyInstancesOf(NamedValue::class, $updated);
	}

	public function testSetPropertyWithVariousTypes(): void
	{
		$input = new TestUpdateInput();
		$input->setName('');
		$input->setEmail('test@example.com');
		$input->setAge(0);

		$updated = $input->getUpdatedProperties();

		$this->assertCount(3, $updated);
		$this->assertSame('', $updated[0]->getValue());
		$this->assertSame('test@example.com', $updated[1]->getValue());
		$this->assertSame(0, $updated[2]->getValue());
	}

	public function testAddUpdatedPropertyFluentInterface(): void
	{
		$input = new TestUpdateInput();
		$result = $input->addManualUpdate(TestProperty::NAME);

		$this->assertSame($input, $result);
	}
}
