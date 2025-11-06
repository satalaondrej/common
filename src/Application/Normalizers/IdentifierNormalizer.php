<?php
declare(strict_types=1);

namespace Nalgoo\Common\Application\Normalizers;

use Nalgoo\Common\Domain\IntegerIdentifier;
use Nalgoo\Common\Domain\StringIdentifier;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class IdentifierNormalizer implements DenormalizerInterface
{
	/**
	 * @return array<class-string, bool>
	 */
	public function getSupportedTypes(?string $format): array
	{
		return [
			StringIdentifier::class => true,
			IntegerIdentifier::class => true,
		];
	}

	/**
	 * @param array<string, mixed> $context
	 *
	 * @return StringIdentifier|IntegerIdentifier
	 */
	public function denormalize($data, string $type, ?string $format = null, array $context = [])
	{
		if (!$this->supportsDenormalization($data, $type)) {
			throw new InvalidArgumentException();
		}

		/** @var StringIdentifier|IntegerIdentifier */
		return new $type($data);
	}

	public function supportsDenormalization($data, string $type, ?string $format = null): bool
	{
		return (is_subclass_of($type, StringIdentifier::class) && is_string($data))
			|| (is_subclass_of($type, IntegerIdentifier::class) && is_int($data));
	}
}
