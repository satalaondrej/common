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
		if ($this->isDoctrineProxy($object) && !$this->isProxyInitialized($object)) {
			$this->loadProxy($object);
		}

		return parent::normalize($object, $format, $context);
	}

	/**
	 * @param class-string|object  $classOrObject
	 * @param array<string, mixed> $context
	 */
	protected function isAllowedAttribute($classOrObject, string $attribute, ?string $format = null, array $context = []): bool
	{
		if ($this->isDoctrineProxyClass($classOrObject) && str_starts_with($attribute, '__')) {
			return false;
		}

		return parent::isAllowedAttribute($classOrObject, $attribute, $format, $context);
	}

	private function isDoctrineProxy(mixed $object): bool
	{
		if (!is_object($object)) {
			return false;
		}

		// Support both ORM 2.x and 3.x
		if (interface_exists(\Doctrine\Persistence\Proxy::class)) {
			if ($object instanceof \Doctrine\Persistence\Proxy) {
				return true;
			}
		}

		if (interface_exists(\Doctrine\Common\Proxy\Proxy::class)) {
			if ($object instanceof \Doctrine\Common\Proxy\Proxy) {
				return true;
			}
		}

		return false;
	}

	private function isDoctrineProxyClass(string|object $classOrObject): bool
	{
		$className = is_object($classOrObject) ? get_class($classOrObject) : $classOrObject;

		// Support both ORM 2.x and 3.x
		if (interface_exists(\Doctrine\Persistence\Proxy::class)) {
			if (is_subclass_of($className, \Doctrine\Persistence\Proxy::class)) {
				return true;
			}
		}

		if (interface_exists(\Doctrine\Common\Proxy\Proxy::class)) {
			if (is_subclass_of($className, \Doctrine\Common\Proxy\Proxy::class)) {
				return true;
			}
		}

		return false;
	}

	private function isProxyInitialized(object $proxy): bool
	{
		// Both ORM versions use the same method name
		if (method_exists($proxy, '__isInitialized')) {
			return $proxy->__isInitialized();
		}

		// If method doesn't exist, consider it initialized
		return true;
	}

	private function loadProxy(object $proxy): void
	{
		// Both ORM versions use the same method name
		if (method_exists($proxy, '__load')) {
			$proxy->__load();
		}
	}
}
