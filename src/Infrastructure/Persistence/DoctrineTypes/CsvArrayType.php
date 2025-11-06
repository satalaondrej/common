<?php
declare(strict_types=1);

namespace Nalgoo\Common\Infrastructure\Persistence\DoctrineTypes;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\SimpleArrayType;
use Nalgoo\Common\Infrastructure\Persistence\PersistenceException;

class CsvArrayType extends SimpleArrayType
{
	public const NAME = 'csv_array';

	public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
	{
		if (!$value) {
			return null;
		}

		return $this->toCsv($value);
	}

	/**
	 * @return string[]
	 */
	public function convertToPHPValue($value, AbstractPlatform $platform): array
	{
		if ($value === null || $value === false || $value === '') {
			return [];
		}

		if (is_resource($value)) {
			$contents = stream_get_contents($value);
			if ($contents === false) {
				throw new PersistenceException('Failed to read contents from database resource stream');
			}
			$value = $contents;

			// After reading from resource, check if empty
			if ($value === '') {
				return [];
			}
		}

		return $this->fromCsv($value);
	}

	public function getName(): string
	{
		return self::NAME;
	}

	/**
	 * @param string[] $data
	 */
	private function toCsv(array $data): string
	{
		$buffer = fopen('php://memory', 'r+');
		if ($buffer === false) {
			throw new PersistenceException('Failed to open PHP memory stream for CSV encoding');
		}

		$writeResult = fputcsv($buffer, $data);
		if ($writeResult === false) {
			fclose($buffer);
			throw new PersistenceException('Failed to write CSV data to memory buffer');
		}

		rewind($buffer);
		$formatted = fgets($buffer);
		fclose($buffer);

		if ($formatted === false) {
			throw new PersistenceException('Failed to read CSV data from memory buffer');
		}

		return $formatted;
	}

	/**
	 * @return string[]
	 */
	private function fromCsv(string $s): array
	{
		$buffer = fopen('php://memory', 'r+');
		if ($buffer === false) {
			throw new PersistenceException('Failed to open PHP memory stream for CSV decoding');
		}

		$writeResult = fwrite($buffer, $s);
		if ($writeResult === false) {
			fclose($buffer);
			throw new PersistenceException('Failed to write string to memory buffer');
		}

		rewind($buffer);
		$data = fgetcsv($buffer);
		fclose($buffer);

		if ($data === false) {
			throw new PersistenceException('Failed to parse CSV data from string');
		}

		/* @var string[] */
		return array_map(fn ($item) => (string) $item, $data);
	}
}
