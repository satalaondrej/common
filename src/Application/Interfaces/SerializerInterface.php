<?php
declare(strict_types=1);

namespace Nalgoo\Common\Application\Interfaces;

use Nalgoo\Common\Application\Exceptions\DeserializeException;

interface SerializerInterface
{
	public const LIST_GROUP = 'list';

	/**
	 * @param object|mixed[]|null $data
	 * @param string[]|null       $groups
	 */
	public function serialize(object|array|null $data, ?array $groups = null): string;

	/**
	 * Deserialize string into an object of supplied class name.
	 *
	 * @template TObject of object
	 *
	 * @param class-string<TObject> $className
	 *
	 * @return TObject|mixed[]
	 *
	 * @throws DeserializeException
	 */
	public function deserialize(string $data, string $className): object|array;
}
