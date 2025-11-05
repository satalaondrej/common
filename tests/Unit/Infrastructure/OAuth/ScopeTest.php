<?php
declare(strict_types=1);

namespace Nalgoo\Common\Tests\Unit\Infrastructure\OAuth;

use Nalgoo\Common\Infrastructure\OAuth\Scope;
use PHPUnit\Framework\TestCase;

final class ScopeTest extends TestCase
{
	public function testConstructorAndGetIdentifier(): void
	{
		$scope = new Scope('read:users');

		$this->assertSame('read:users', $scope->getIdentifier());
	}

	public function testIsSatisfiedByReturnsTrueForSameIdentifier(): void
	{
		$scope1 = new Scope('read:users');
		$scope2 = new Scope('read:users');

		$this->assertTrue($scope1->isSatisfiedBy($scope2));
		$this->assertTrue($scope2->isSatisfiedBy($scope1));
	}

	public function testIsSatisfiedByReturnsFalseForDifferentIdentifiers(): void
	{
		$scope1 = new Scope('read:users');
		$scope2 = new Scope('write:users');

		$this->assertFalse($scope1->isSatisfiedBy($scope2));
		$this->assertFalse($scope2->isSatisfiedBy($scope1));
	}

	public function testIsSatisfiedBySameInstance(): void
	{
		$scope = new Scope('admin');

		$this->assertTrue($scope->isSatisfiedBy($scope));
	}

	public function testVariousIdentifiers(): void
	{
		$identifiers = [
			'read',
			'write',
			'admin',
			'user:profile',
			'api:access',
			'resource:read:all',
		];

		foreach ($identifiers as $identifier) {
			$scope = new Scope($identifier);
			$this->assertSame($identifier, $scope->getIdentifier());
		}
	}

	public function testEmptyIdentifier(): void
	{
		$scope = new Scope('');

		$this->assertSame('', $scope->getIdentifier());
	}

	public function testIdentifierWithSpecialCharacters(): void
	{
		$scope = new Scope('scope-with-dashes_and_underscores.and.dots');

		$this->assertSame('scope-with-dashes_and_underscores.and.dots', $scope->getIdentifier());
	}

	public function testCaseSensitiveIdentifiers(): void
	{
		$scope1 = new Scope('Read');
		$scope2 = new Scope('read');

		$this->assertFalse($scope1->isSatisfiedBy($scope2));
		$this->assertFalse($scope2->isSatisfiedBy($scope1));
	}

	public function testIsSatisfiedByWithWhitespace(): void
	{
		$scope1 = new Scope('read:users');
		$scope2 = new Scope('read:users ');

		$this->assertFalse($scope1->isSatisfiedBy($scope2));
	}
}
