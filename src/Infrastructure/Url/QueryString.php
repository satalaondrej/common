<?php
declare(strict_types=1);

namespace Nalgoo\Common\Infrastructure\Url;

class QueryString implements \Stringable
{
	private bool $separator = false;

	/**
	 * @param array<string, mixed> $params
	 */
	public function __construct(
		private array $params,
	) {
	}

	/**
	 * @param array<string, mixed> $params
	 * @return static
	 */
	public static function new(array $params = []): static
	{
		/** @phpstan-ignore-next-line */
		return new static($params);
	}

	/**
	 * Add or update param with given name with new value
	 */
	public function withParam(string $name, mixed $value): static
	{
		$clone = clone $this;
		$clone->params[$name] = $value;
		return $clone;
	}

	/**
	 * Replace all params with supplied array
	 * @param array<string, mixed> $params
	 */
	public function withParams(array $params): static
	{
		$clone = clone $this;
		$clone->params = $params;
		return $clone;
	}

	/**
	 * Render query string with separator character `?`
	 */
	public function withSeparator(): static
	{
		$clone = clone $this;
		$clone->separator = true;
		return $clone;
	}

	public function __toString(): string
	{
		$query = http_build_query($this->params, '', '&', PHP_QUERY_RFC3986);
		return (($this->separator && count($this->params) > 0) ? '?' : '') . $query;
	}
}