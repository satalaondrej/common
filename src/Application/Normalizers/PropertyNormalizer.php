<?php
declare(strict_types=1);

namespace Nalgoo\Common\Application\Normalizers;

use Symfony\Component\Serializer\Normalizer\PropertyNormalizer as BasePropertyNormalizer;

/**
 * Normalizer - acts same as Symfony PropertyNormalizer but serializes doctrine proxies properly
 * (initializes /loads them and ignores doctrine internal proxy properties).
 *
 * Supports both Doctrine ORM 2.x (Doctrine\Common\Proxy\Proxy)
 * and ORM 3.x (Doctrine\Persistence\Proxy).
 *
 * @phpstan-ignore class.extendsFinalByPhpDoc (intentional extension to add Doctrine proxy support)
 **/
class PropertyNormalizer extends BasePropertyNormalizer
{
	/**
	 * @param array<string, mixed> $context
	 *
	 * @return float|int|bool|string|mixed[]|\ArrayObject<int|string, mixed>|null
	 */
	public function normalize($object, ?string $format = null, array $context = []): float|array|\ArrayObject|bool|int|string|null
	{
		// Support both ORM 2.x (Doctrine\Common\Proxy\Proxy) and 3.x (Doctrine\Persistence\Proxy)
		if ((interface_exists(\Doctrine\Persistence\Proxy::class) && $object instanceof \Doctrine\Persistence\Proxy)
			|| (interface_exists(\Doctrine\Common\Proxy\Proxy::class) && $object instanceof \Doctrine\Common\Proxy\Proxy)
		) {
			// @phpstan-ignore class.notFound (supports both ORM versions - interfaces may not exist)
			if (method_exists($object, '__isInitialized') && !$object->__isInitialized()) {
				// @phpstan-ignore class.notFound (supports both ORM versions - interfaces may not exist)
				$object->__load();
			}
		}

		return parent::normalize($object, $format, $context);
	}

	/**
	 * @param class-string|object  $classOrObject
	 * @param array<string, mixed> $context
	 */
	protected function isAllowedAttribute($classOrObject, string $attribute, ?string $format = null, array $context = []): bool
	{
		// Ignore Doctrine proxy internal properties
		$className = is_object($classOrObject) ? get_class($classOrObject) : $classOrObject;
		if (str_starts_with($attribute, '__')
			&& ((interface_exists(\Doctrine\Persistence\Proxy::class) && is_subclass_of($className, \Doctrine\Persistence\Proxy::class))
				|| (interface_exists(\Doctrine\Common\Proxy\Proxy::class) && is_subclass_of($className, \Doctrine\Common\Proxy\Proxy::class)))
		) {
			return false;
		}

		return parent::isAllowedAttribute($classOrObject, $attribute, $format, $context);
	}
}
