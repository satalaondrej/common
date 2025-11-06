<?php
declare(strict_types=1);

namespace Nalgoo\Common\Tests\Unit\Application;

use Nalgoo\Common\Application\Exceptions\DeserializeException;
use Nalgoo\Common\Application\Serializer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\SerializerInterface as SymfonySerializerInterface;
use Webmozart\Assert\InvalidArgumentException;

final class SerializerTest extends TestCase
{
	private Serializer $serializer;
	/** @var MockObject&SymfonySerializerInterface */
	private SymfonySerializerInterface $symfonySerializer;

	protected function setUp(): void
	{
		$this->symfonySerializer = $this->createMock(SymfonySerializerInterface::class);
		$this->serializer = new Serializer($this->symfonySerializer);
	}

	public function testConstructorSetsSerializer(): void
	{
		$serializer = new Serializer($this->symfonySerializer);

		$this->assertInstanceOf(Serializer::class, $serializer);
	}

	public function testSerializeWithoutGroups(): void
	{
		$data = ['name' => 'John', 'email' => 'john@example.com'];

		$this->symfonySerializer
			->expects($this->once())
			->method('serialize')
			->with($data, 'json', [])
			->willReturn('{"name":"John","email":"john@example.com"}');

		$result = $this->serializer->serialize($data);

		$this->assertSame('{"name":"John","email":"john@example.com"}', $result);
	}

	public function testSerializeWithGroups(): void
	{
		$data = ['name' => 'John'];
		$groups = ['public', 'api'];

		$this->symfonySerializer
			->expects($this->once())
			->method('serialize')
			->with($data, 'json', ['groups' => $groups])
			->willReturn('{"name":"John"}');

		$result = $this->serializer->serialize($data, $groups);

		$this->assertSame('{"name":"John"}', $result);
	}

	public function testSerializeWithSingleGroup(): void
	{
		$data = ['id' => 1];
		$groups = ['admin'];

		$this->symfonySerializer
			->expects($this->once())
			->method('serialize')
			->with($data, 'json', ['groups' => $groups])
			->willReturn('{"id":1}');

		$result = $this->serializer->serialize($data, $groups);

		$this->assertSame('{"id":1}', $result);
	}

	public function testSerializeWithEmptyGroupsThrowsException(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Serializer groups must be array of strings!');

		$this->serializer->serialize(['data'], ['']);
	}

	public function testSerializeObject(): void
	{
		$object = new \stdClass();
		$object->property = 'value';

		$this->symfonySerializer
			->expects($this->once())
			->method('serialize')
			->with($object, 'json', [])
			->willReturn('{"property":"value"}');

		$result = $this->serializer->serialize($object);

		$this->assertSame('{"property":"value"}', $result);
	}

	public function testDeserializeSuccess(): void
	{
		$json = '{"name":"John"}';
		$expectedObject = new \stdClass();
		$expectedObject->name = 'John';

		$this->symfonySerializer
			->expects($this->once())
			->method('deserialize')
			->with($json, \stdClass::class, 'json')
			->willReturn($expectedObject);

		$result = $this->serializer->deserialize($json, \stdClass::class);

		$this->assertSame($expectedObject, $result);
	}

	public function testDeserializeArray(): void
	{
		$json = '[1,2,3]';
		$expectedArray = [1, 2, 3];

		$this->symfonySerializer
			->expects($this->once())
			->method('deserialize')
			->with($json, 'array', 'json')
			->willReturn($expectedArray);

		// @phpstan-ignore argument.type (testing array deserialization)
		$result = $this->serializer->deserialize($json, 'array');

		$this->assertSame($expectedArray, $result);
	}

	public function testDeserializeThrowsDeserializeException(): void
	{
		$json = 'invalid json';

		$this->symfonySerializer
			->expects($this->once())
			->method('deserialize')
			->with($json, \stdClass::class, 'json')
			->willThrowException(new \RuntimeException('Invalid JSON'));

		$this->expectException(DeserializeException::class);
		$this->expectExceptionMessage('Deserialization failed: Invalid JSON');

		$this->serializer->deserialize($json, \stdClass::class);
	}

	public function testDeserializeWrapsThrowableInDeserializeException(): void
	{
		$json = '{}';

		$this->symfonySerializer
			->expects($this->once())
			->method('deserialize')
			->willThrowException(new \Error('PHP Error'));

		$this->expectException(DeserializeException::class);
		$this->expectExceptionMessage('Deserialization failed: PHP Error');

		$this->serializer->deserialize($json, \stdClass::class);
	}

	public function testFormatConstant(): void
	{
		$this->assertSame('json', Serializer::FORMAT);
	}

	public function testSerializeUsesJsonFormat(): void
	{
		$data = ['test' => 'value'];

		$this->symfonySerializer
			->expects($this->once())
			->method('serialize')
			->with($this->anything(), 'json', $this->anything())
			->willReturn('{}');

		$this->serializer->serialize($data);
	}

	public function testDeserializeUsesJsonFormat(): void
	{
		$json = '{}';

		$this->symfonySerializer
			->expects($this->once())
			->method('deserialize')
			->with($this->anything(), $this->anything(), 'json')
			->willReturn(new \stdClass());

		$this->serializer->deserialize($json, \stdClass::class);
	}
}
