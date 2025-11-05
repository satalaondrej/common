<?php
declare(strict_types=1);

namespace Nalgoo\Common\Application\Normalizers;

use Doctrine\Common\Proxy\Proxy;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer as BasePropertyNormalizer;


/**
 * Normalizer - acts same as Symfony PropertyNormalizer but serializes doctrine proxies properly
 * (initializes /loads them and ignores doctrine internal proxy properties)
 **/
class PropertyNormalizer extends BasePropertyNormalizer
{
	/**
	 * @param mixed $object
	 * @param string|null $format
	 * @param array<string, mixed> $context
	 * @return float|int|bool|string|null|array<mixed>|\ArrayObject<int|string, mixed>
	 */
	public function normalize($object, string $format = null, array $context = []): float|array|\ArrayObject|bool|int|string|null
	{
		if ($object instanceof Proxy && !$object->__isInitialized()) {
			$object->__load();
		}

		return parent::normalize($object, $format, $context);
	}

	/**
	 * {@inheritdoc}
	 * @param class-string|object $classOrObject
	 * @param string $attribute
	 * @param string|null $format
	 * @param array<string, mixed> $context
	 * @return bool
	 */
    protected function isAllowedAttribute($classOrObject, string $attribute, string $format = null, array $context = []): bool
    {
		if (is_subclass_of($classOrObject, Proxy::class) && str_starts_with($attribute, '__')) {
			return false;
		}

        return parent::isAllowedAttribute($classOrObject, $attribute, $format, $context);
    }
}
