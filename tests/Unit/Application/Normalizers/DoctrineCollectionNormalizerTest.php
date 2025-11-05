<?php
declare(strict_types=1);

namespace Nalgoo\Common\Tests\Unit\Application\Normalizers;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Nalgoo\Common\Application\Normalizers\DoctrineCollectionNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class DoctrineCollectionNormalizerTest extends TestCase
{
	public function testGetSupportedTypesWithDefaultTrue(): void
	{
		$normalizer = new DoctrineCollectionNormalizer(useAsDefault: true);
		$types = $normalizer->getSupportedTypes(null);

		$this->assertArrayHasKey(Collection::class, $types);
		$this->assertTrue($types[Collection::class]);
	}

	public function testGetSupportedTypesWithDefaultFalse(): void
	{
		$normalizer = new DoctrineCollectionNormalizer(useAsDefault: false);
		$types = $normalizer->getSupportedTypes(null);

		$this->assertArrayHasKey(Collection::class, $types);
		$this->assertFalse($types[Collection::class]);
	}

	public function testSupportsNormalizationWithDefaultTrue(): void
	{
		$normalizer = new DoctrineCollectionNormalizer(useAsDefault: true);
		$collection = new ArrayCollection([1, 2, 3]);

		$this->assertTrue($normalizer->supportsNormalization($collection));
	}

	public function testSupportsNormalizationWithDefaultFalse(): void
	{
		$normalizer = new DoctrineCollectionNormalizer(useAsDefault: false);
		$collection = new ArrayCollection([1, 2, 3]);

		$this->assertFalse($normalizer->supportsNormalization($collection));
	}

	public function testSupportsNormalizationWithContextTrue(): void
	{
		$normalizer = new DoctrineCollectionNormalizer(useAsDefault: false);
		$collection = new ArrayCollection([1, 2, 3]);

		$this->assertTrue($normalizer->supportsNormalization(
			$collection,
			null,
			[DoctrineCollectionNormalizer::SERIALIZE_COLLECTION_WITHOUT_KEYS => true]
		));
	}

	public function testSupportsNormalizationWithContextFalse(): void
	{
		$normalizer = new DoctrineCollectionNormalizer(useAsDefault: true);
		$collection = new ArrayCollection([1, 2, 3]);

		$this->assertFalse($normalizer->supportsNormalization(
			$collection,
			null,
			[DoctrineCollectionNormalizer::SERIALIZE_COLLECTION_WITHOUT_KEYS => false]
		));
	}

	public function testSupportsNormalizationReturnsFalseForNonCollection(): void
	{
		$normalizer = new DoctrineCollectionNormalizer(useAsDefault: true);

		$this->assertFalse($normalizer->supportsNormalization([]));
		$this->assertFalse($normalizer->supportsNormalization(new \stdClass()));
		$this->assertFalse($normalizer->supportsNormalization('string'));
	}

	public function testNormalizeWithArrayCollection(): void
	{
		$normalizer = new DoctrineCollectionNormalizer(useAsDefault: true);
		$collection = new ArrayCollection(['a' => 1, 'b' => 2, 'c' => 3]);

		$mockNormalizer = $this->createMock(NormalizerInterface::class);
		$mockNormalizer
			->expects($this->once())
			->method('normalize')
			->with([1, 2, 3], null, [])
			->willReturn([1, 2, 3]);

		$normalizer->setNormalizer($mockNormalizer);

		$result = $normalizer->normalize($collection);

		$this->assertSame([1, 2, 3], $result);
	}

	public function testNormalizeCallsGetValues(): void
	{
		$normalizer = new DoctrineCollectionNormalizer();
		$collection = new ArrayCollection([10 => 'value1', 20 => 'value2']);

		$mockNormalizer = $this->createMock(NormalizerInterface::class);
		$mockNormalizer
			->expects($this->once())
			->method('normalize')
			->with(['value1', 'value2'], null, [])
			->willReturn(['value1', 'value2']);

		$normalizer->setNormalizer($mockNormalizer);

		$result = $normalizer->normalize($collection);

		$this->assertSame(['value1', 'value2'], $result);
	}

	public function testNormalizeWithFormat(): void
	{
		$normalizer = new DoctrineCollectionNormalizer();
		$collection = new ArrayCollection([1, 2]);

		$mockNormalizer = $this->createMock(NormalizerInterface::class);
		$mockNormalizer
			->expects($this->once())
			->method('normalize')
			->with([1, 2], 'json', [])
			->willReturn([1, 2]);

		$normalizer->setNormalizer($mockNormalizer);

		$result = $normalizer->normalize($collection, 'json');

		$this->assertSame([1, 2], $result);
	}

	public function testNormalizeWithContext(): void
	{
		$normalizer = new DoctrineCollectionNormalizer();
		$collection = new ArrayCollection([1, 2]);
		$context = ['groups' => ['api']];

		$mockNormalizer = $this->createMock(NormalizerInterface::class);
		$mockNormalizer
			->expects($this->once())
			->method('normalize')
			->with([1, 2], null, $context)
			->willReturn([1, 2]);

		$normalizer->setNormalizer($mockNormalizer);

		$result = $normalizer->normalize($collection, null, $context);

		$this->assertSame([1, 2], $result);
	}

	public function testNormalizeThrowsExceptionForNonCollection(): void
	{
		$normalizer = new DoctrineCollectionNormalizer(useAsDefault: false);

		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('The object must be instance of doctrine Collection!');

		$normalizer->normalize([1, 2, 3]);
	}

	public function testNormalizeEmptyCollection(): void
	{
		$normalizer = new DoctrineCollectionNormalizer();
		$collection = new ArrayCollection([]);

		$mockNormalizer = $this->createMock(NormalizerInterface::class);
		$mockNormalizer
			->expects($this->once())
			->method('normalize')
			->with([], null, [])
			->willReturn([]);

		$normalizer->setNormalizer($mockNormalizer);

		$result = $normalizer->normalize($collection);

		$this->assertSame([], $result);
	}

	public function testConstant(): void
	{
		$this->assertSame('serialize-collection-without-keys', DoctrineCollectionNormalizer::SERIALIZE_COLLECTION_WITHOUT_KEYS);
	}
}
