<?php
declare(strict_types=1);

namespace Nalgoo\Common\Infrastructure\OAuth;

use Lcobucci\Clock\FrozenClock;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\UnencryptedToken;
use Lcobucci\JWT\Validation\Constraint\LooseValidAt;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Validator;
use Nalgoo\Common\Infrastructure\Clock\ClockService;
use Nalgoo\Common\Infrastructure\OAuth\Exceptions\OAuthScopeException;
use Nalgoo\Common\Infrastructure\OAuth\Exceptions\OAuthTokenException;
use Psr\Http\Message\ServerRequestInterface;

class ResourceServer
{
	private const TOKEN_LEEWAY = 'PT5S';

	public function __construct(
		protected Key $publicKey,
		protected ClockService $clockService,
	) {
	}

	/**
	 * @throws OAuthScopeException
	 * @throws OAuthTokenException
	 */
	public function getValidToken(ServerRequestInterface $request, ScopeInterface $requiredScope): UnencryptedToken
	{
		$token = $this->validateToken($request);

		$this->validateScope($token, $requiredScope);

		return $token;
	}

	/**
	 * @throws OAuthScopeException
	 */
	protected function validateScope(UnencryptedToken $token, ScopeInterface $requiredScope): void
	{
		$scopes = array_map(
			fn ($scopeIdentifier) => new Scope($scopeIdentifier),
			(array) $token->claims()->get('scopes', []),
		);

		foreach ($scopes as $scope) {
			if ($requiredScope->isSatisfiedBy($scope)) {
				return;
			}
		}

		throw new OAuthScopeException('Token is missing required scope: '.$requiredScope->getIdentifier());
	}

	/**
	 * Taken from https://oauth2.thephpleague.com/.
	 *
	 * @throws OAuthTokenException
	 */
	protected function validateToken(ServerRequestInterface $request): UnencryptedToken
	{
		if ($request->hasHeader('authorization') === false) {
			throw new OAuthTokenException('Missing "Authorization" header');
		}

		$header = $request->getHeader('authorization');
		$jwt = $this->extractBearerToken($header[0]);

		// Attempt to parse and validate the JWT

		$validator = new Validator();

		try {
			$token = (new Parser(new JoseEncoder()))->parse($jwt);
		} catch (\Throwable $e) {
			throw new OAuthTokenException('Cannot parse JWT token: '.$e->getMessage());
		}

		if (!$token instanceof UnencryptedToken) {
			throw new OAuthTokenException('Token is not an unencrypted token');
		}

		if (!$validator->validate($token, new SignedWith(new Sha256(), $this->publicKey))) {
			throw new OAuthTokenException('Access token signature could not be verified');
		}

		$clock = new FrozenClock($this->clockService->getCurrentTime());

		if (!$validator->validate($token, new LooseValidAt($clock, new \DateInterval(self::TOKEN_LEEWAY)))) {
			$this->throwExpiredTokenException($clock, $token);
		}

		return $token;
	}

	private function extractBearerToken(string $header): string
	{
		$result = preg_replace('/^(?:\s+)?Bearer\s/', '', $header);

		if ($result === null) {
			throw new OAuthTokenException('Failed to parse Authorization header');
		}

		return trim($result);
	}

	private function throwExpiredTokenException(FrozenClock $clock, UnencryptedToken $token): never
	{
		$message = sprintf(
			'Access token is expired: [now=%d] [token iat=%s, nbf=%s, exp=%s, sub=%s]',
			$clock->now()->getTimestamp(),
			$token->claims()->get('iat')?->getTimestamp() ?? '',
			$token->claims()->get('nbf')?->getTimestamp() ?? '',
			$token->claims()->get('exp')?->getTimestamp() ?? '',
			$token->claims()->get('sub')
		);

		throw new OAuthTokenException($message);
	}
}
