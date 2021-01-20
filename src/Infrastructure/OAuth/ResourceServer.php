<?php
declare(strict_types=1);

namespace Nalgoo\Common\Infrastructure\OAuth;

use Lcobucci\Clock\FrozenClock;
use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Constraint\ValidAt;
use Lcobucci\JWT\Validation\Validator;
use Lcobucci\JWT\ValidationData;
use Nalgoo\Common\Infrastructure\Clock\ClockService;
use Nalgoo\Common\Infrastructure\OAuth\Exceptions\OAuthAudienceException;
use Nalgoo\Common\Infrastructure\OAuth\Exceptions\OAuthScopeException;
use Nalgoo\Common\Infrastructure\OAuth\Exceptions\OAuthTokenException;
use Psr\Http\Message\ServerRequestInterface;

class ResourceServer
{
	private Key $publicKey;

	private ClockService $clockService;

	public function __construct(Key $publicKey, ClockService $clockService)
	{
		$this->publicKey = $publicKey;
		$this->clockService = $clockService;
	}

	/**
	 * @throws OAuthTokenException
	 * @throws OAuthAudienceException
	 * @throws OAuthScopeException
	 */
	public function getValidToken(ServerRequestInterface $request, ScopeInterface $requiredScope): Token
	{
		$token = $this->validateToken($request);

		$this->validateScope($token, $requiredScope);

		return $token;
	}

	/**
	 * @throws OAuthScopeException
	 */
	protected function validateScope(Token $token, ScopeInterface $requiredScope): bool
	{
		$scopes = array_map(
			fn ($scopeIdentifier) => new Scope($scopeIdentifier),
			(array) $token->claims()->get('scopes', []),
		);

		foreach ($scopes as $scope) {
			if ($requiredScope->isSatisfiedBy($scope)) {
				return true;
			}
		}

		throw new OAuthScopeException('Token is missing required scope: ' . $requiredScope->getIdentifier());
	}

	/**
	 * Taken from https://oauth2.thephpleague.com/
	 *
	 * @throws OAuthTokenException
	 */
	protected function validateToken(ServerRequestInterface $request): Token
	{
		if ($request->hasHeader('authorization') === false) {
			throw new OAuthTokenException('Missing "Authorization" header');
		}

		$header = $request->getHeader('authorization');
		$jwt = trim((string) preg_replace('/^(?:\s+)?Bearer\s/', '', $header[0]));

		// Attempt to parse and validate the JWT

		$validator = new Validator();

		try {
			$token = (new Parser(new JoseEncoder()))->parse($jwt);
		} catch (\Throwable $e) {
			throw new OAuthTokenException('Cannot parse JWT token: ' . $e->getMessage());
		}

		if (!$validator->validate($token, new SignedWith(new Sha256(), $this->publicKey))) {
			throw new OAuthTokenException('Access token signature could not be verified');
		}

		$clock = new FrozenClock($this->clockService->getCurrentTime());

		if (!$validator->validate($token, new ValidAt($clock, new \DateInterval('PT5S')))) {
			throw new OAuthTokenException('Access token is expired');
		}

		return $token;
	}

}
