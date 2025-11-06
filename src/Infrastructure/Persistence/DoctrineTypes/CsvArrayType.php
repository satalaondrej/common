<?php
declare(strict_types=1);

namespace Nalgoo\Common\Infrastructure\Persistence\DoctrineTypes;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\SimpleArrayType;

class CsvArrayType extends SimpleArrayType
{
	public const NAME = 'csv_array';

	public function convertToDatabaseValue($value, AbstractPlatform $platform)
	{
		if (!$value) {
			return null;
		}

		$result = $this->toCsv($value);

		return is_string($result) ? $result : null;
	}

	public function convertToPHPValue($value, AbstractPlatform $platform)
	{
		if ($value === null) {
			return [];
		}

		$value = is_resource($value) ? stream_get_contents($value) : $value;

		if ($value === false) {
			return [];
		}

		$result = $this->fromCsv($value);

		return is_array($result) ? array_values($result) : [];
	}

	public function getName(): string
	{
		return self::NAME;
	}

	/**
	 * @param string[] $data
	 */
	private function toCsv(array $data): bool|string
	{
		$buffer = fopen('php://memory', 'r+');

		if ($buffer === false) {
			return false;
		}

		fputcsv($buffer, $data);
		rewind($buffer);
		$formatted = fgets($buffer);
		fclose($buffer);

		return $formatted;
	}

	/**
	 * @return string[]|false
	 */
	private function fromCsv(string $s): bool|array
	{
		$buffer = fopen('php://memory', 'r+');

		if ($buffer === false) {
			return false;
		}

		fwrite($buffer, $s);
		rewind($buffer);
		$data = fgetcsv($buffer);
		fclose($buffer);

		if ($data === false) {
			return false;
		}

		/* @var string[] */
		return array_map(fn ($item) => (string) $item, $data);
	}
}
