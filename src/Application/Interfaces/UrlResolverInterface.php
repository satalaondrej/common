<?php
declare(strict_types=1);

namespace Nalgoo\Common\Application\Interfaces;

interface UrlResolverInterface
{
	/**
	 * @param array<string, mixed> $queryParams
	 */
	public function resolveUrl(string $path, array $queryParams = []): string;
}
