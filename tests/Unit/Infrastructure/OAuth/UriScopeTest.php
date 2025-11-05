<?php
declare(strict_types=1);

namespace Nalgoo\Common\Tests\Unit\Infrastructure\OAuth;

use Nalgoo\Common\Infrastructure\OAuth\ResourceServerConfig;
use Nalgoo\Common\Infrastructure\OAuth\Scope;
use Nalgoo\Common\Infrastructure\OAuth\UriScope;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\InvalidArgumentException;

final class UriScopeTest extends TestCase
{
	private ResourceServerConfig $config;

	protected function setUp(): void
	{
		$this->config = new ResourceServerConfig('example.com');
	}

	public function testConstructorWithValidPath(): void
	{
		$scope = new UriScope('api/users', $this->config);

		$this->assertSame('https://example.com/auth/api/users', $scope->getIdentifier());
	}

	public function testConstructorWithEmptyPath(): void
	{
		$scope = new UriScope('', $this->config);

		$this->assertSame('https://example.com/auth', $scope->getIdentifier());
	}

	public function testConstructorWithDotsInPath(): void
	{
		$scope = new UriScope('some.thing.nice', $this->config);

		$this->assertSame('https://example.com/auth/some.thing.nice', $scope->getIdentifier());
	}

	public function testConstructorWithHyphensInPath(): void
	{
		$scope = new UriScope('api-v1/users-list', $this->config);

		$this->assertSame('https://example.com/auth/api-v1/users-list', $scope->getIdentifier());
	}

	public function testConstructorThrowsExceptionForInvalidCharacters(): void
	{
		$this->expectException(InvalidArgumentException::class);
		new UriScope('api/Users', $this->config); // uppercase not allowed
	}

	public function testConstructorThrowsExceptionForSpaces(): void
	{
		$this->expectException(InvalidArgumentException::class);
		new UriScope('api users', $this->config);
	}

	public function testConstructorThrowsExceptionForSpecialChars(): void
	{
		$this->expectException(InvalidArgumentException::class);
		new UriScope('api@users', $this->config);
	}

	public function testGetIdentifierRemovesTrailingSlash(): void
	{
		$scope = new UriScope('api/users/', $this->config);

		$this->assertSame('https://example.com/auth/api/users', $scope->getIdentifier());
	}

	public function testIsSatisfiedByExactMatch(): void
	{
		$scope1 = new UriScope('api/users', $this->config);
		$scope2 = new Scope('https://example.com/auth/api/users');

		$this->assertTrue($scope1->isSatisfiedBy($scope2));
	}

	public function testIsSatisfiedByExactMatchWithTrailingSlash(): void
	{
		$scope1 = new UriScope('api/users', $this->config);
		$scope2 = new Scope('https://example.com/auth/api/users/');

		$this->assertTrue($scope1->isSatisfiedBy($scope2));
	}

	public function testIsSatisfiedByParentPath(): void
	{
		$scope = new UriScope('api/users/read', $this->config);
		$parent = new Scope('https://example.com/auth/api/users');

		$this->assertTrue($scope->isSatisfiedBy($parent));
	}

	public function testIsSatisfiedByRootPath(): void
	{
		$scope = new UriScope('api/users', $this->config);
		$root = new Scope('https://example.com/auth');

		$this->assertTrue($scope->isSatisfiedBy($root));
	}

	public function testIsSatisfiedByParentWithDot(): void
	{
		$scope = new UriScope('api.users.read', $this->config);
		$parent = new Scope('https://example.com/auth/api.users');

		$this->assertTrue($scope->isSatisfiedBy($parent));
	}

	public function testIsSatisfiedByReturnsFalseForNonMatchingScope(): void
	{
		$scope = new UriScope('api/users', $this->config);
		$other = new Scope('https://example.com/auth/api/posts');

		$this->assertFalse($scope->isSatisfiedBy($other));
	}

	public function testIsSatisfiedByReturnsFalseForPartialMatch(): void
	{
		$scope = new UriScope('api/users', $this->config);
		$partial = new Scope('https://example.com/auth/api/user'); // missing 's'

		$this->assertFalse($scope->isSatisfiedBy($partial));
	}

	public function testIsSatisfiedByReturnsFalseForScopeWithoutScheme(): void
	{
		$scope = new UriScope('api/users', $this->config);
		$noScheme = new Scope('example.com/auth/api/users');

		$this->assertFalse($scope->isSatisfiedBy($noScheme));
	}

	public function testIsSatisfiedByReturnsFalseForChildScope(): void
	{
		$scope = new UriScope('api', $this->config);
		$child = new Scope('https://example.com/auth/api/users/read');

		$this->assertFalse($scope->isSatisfiedBy($child));
	}

	public function testIsSatisfiedByWithHttpScheme(): void
	{
		$config = new ResourceServerConfig('localhost', secure: false);
		$scope = new UriScope('api', $config);
		$testScope = new Scope('http://localhost/auth');

		$this->assertTrue($scope->isSatisfiedBy($testScope));
	}

	public function testSetDefaultResourceServerConfig(): void
	{
		$config = new ResourceServerConfig('api.example.com');
		UriScope::setDefaultResourceServerConfig($config);

		$scope = UriScope::withDefaults('users');

		$this->assertSame('https://api.example.com/auth/users', $scope->getIdentifier());
	}

	public function testWithDefaultsThrowsExceptionWhenNotSet(): void
	{
		// Reset static config by creating new instance
		$reflection = new \ReflectionClass(UriScope::class);
		$property = $reflection->getProperty('defaultResourceServerConfig');
		$property->setValue(null, null);

		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage('ResourceServerConfig not set');
		UriScope::withDefaults('api');
	}

	public function testComplexPathMatching(): void
	{
		$scope = new UriScope('dir1/dir2/some.thing.nice', $this->config);

		// All these should match
		$this->assertTrue($scope->isSatisfiedBy(new Scope('https://example.com/auth')));
		$this->assertTrue($scope->isSatisfiedBy(new Scope('https://example.com/auth/dir1')));
		$this->assertTrue($scope->isSatisfiedBy(new Scope('https://example.com/auth/dir1/dir2')));
		$this->assertTrue($scope->isSatisfiedBy(new Scope('https://example.com/auth/dir1/dir2/some')));
		$this->assertTrue($scope->isSatisfiedBy(new Scope('https://example.com/auth/dir1/dir2/some.thing')));
		$this->assertTrue($scope->isSatisfiedBy(new Scope('https://example.com/auth/dir1/dir2/some.thing.nice')));
	}

	public function testPathWithOnlyNumbers(): void
	{
		$scope = new UriScope('api/123/users', $this->config);

		$this->assertSame('https://example.com/auth/api/123/users', $scope->getIdentifier());
	}

	public function testPathWithMultipleSlashes(): void
	{
		$scope = new UriScope('api/v1/users/list', $this->config);

		$this->assertSame('https://example.com/auth/api/v1/users/list', $scope->getIdentifier());
	}
}
