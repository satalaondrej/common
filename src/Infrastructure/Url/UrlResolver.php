<?php
declare(strict_types=1);

namespace Nalgoo\Common\Infrastructure\Url;

use League\Uri\Uri;
use Nalgoo\Common\Application\Interfaces\UrlResolverInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

class UrlResolver implements UrlResolverInterface
{
	public function __construct(
		private RequestInterface $request
	)
	{
	}

	/**
	 * @param array<string, mixed> $queryParams
	 */
	public function resolveUrl(string|\Stringable $path, array $queryParams = []): string
	{
		$uri = self::versionAwareCreateUri($path, $this->request->getUri());

		// clear user/password from URI, which can be set in client credentials flow
		$uri = $uri->withUserInfo(null);

		if ($queryParams) {
			$uri = $uri->withQuery(http_build_query($queryParams, '', '&', PHP_QUERY_RFC3986));
		}

		return (string) $uri;
	}

	private static function versionAwareCreateUri(string|\Stringable $uri, UriInterface $baseUri): Uri
	{
		// League URI v7+ uses fromBaseUri, v6 uses createFromBaseUri
		// @phpstan-ignore-next-line function.alreadyNarrowedType (backward compatibility check)
		if (method_exists(Uri::class, 'fromBaseUri')) {
			return Uri::fromBaseUri($uri, $baseUri);
		}
		// @phpstan-ignore-next-line method.notFound
		return Uri::createFromBaseUri($uri, $baseUri);
	}
}
