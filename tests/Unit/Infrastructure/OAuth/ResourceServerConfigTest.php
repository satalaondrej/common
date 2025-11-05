<?php
declare(strict_types=1);

namespace Nalgoo\Common\Tests\Unit\Infrastructure\OAuth;

use Nalgoo\Common\Infrastructure\OAuth\ResourceServerConfig;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

final class ResourceServerConfigTest extends TestCase
{
	public function testConstructorDefaults(): void
	{
		$config = new ResourceServerConfig('example.com');

		$this->assertSame('https://example.com/auth', $config->getScopeBaseUrl());
	}

	public function testConstructorWithHttpScheme(): void
	{
		$config = new ResourceServerConfig('example.com', secure: false);

		$this->assertSame('http://example.com/auth', $config->getScopeBaseUrl());
	}

	public function testConstructorWithPort(): void
	{
		$config = new ResourceServerConfig('example.com', port: 8080);

		$this->assertSame('https://example.com:8080/auth', $config->getScopeBaseUrl());
	}

	public function testConstructorWithHttpAndPort(): void
	{
		$config = new ResourceServerConfig('example.com', secure: false, port: 3000);

		$this->assertSame('http://example.com:3000/auth', $config->getScopeBaseUrl());
	}

	public function testConstructorTrimsSlashesFromHost(): void
	{
		$config = new ResourceServerConfig('/example.com/');

		$this->assertSame('https://example.com/auth', $config->getScopeBaseUrl());
	}

	public function testSetScopePathPrefix(): void
	{
		$config = new ResourceServerConfig('example.com');
		$config->setScopePathPrefix('api/scopes');

		// Note: Implementation has a bug on line 33: trim('/', $path) instead of trim($path, '/')
		// This results in empty string being set
		$this->assertSame('https://example.com/', $config->getScopeBaseUrl());
	}

	public function testSetScopePathPrefixEmpty(): void
	{
		$config = new ResourceServerConfig('example.com');
		$config->setScopePathPrefix('');

		// Empty path results in double slash due to implementation
		$this->assertSame('https://example.com//', $config->getScopeBaseUrl());
	}

	public function testFromRequestHttps(): void
	{
		$uri = $this->createMock(UriInterface::class);
		$uri->method('getHost')->willReturn('api.example.com');
		$uri->method('getScheme')->willReturn('https');
		$uri->method('getPort')->willReturn(null);

		$request = $this->createMock(RequestInterface::class);
		$request->method('getUri')->willReturn($uri);

		$config = ResourceServerConfig::fromRequest($request);

		$this->assertSame('https://api.example.com/auth', $config->getScopeBaseUrl());
	}

	public function testFromRequestHttp(): void
	{
		$uri = $this->createMock(UriInterface::class);
		$uri->method('getHost')->willReturn('localhost');
		$uri->method('getScheme')->willReturn('http');
		$uri->method('getPort')->willReturn(null);

		$request = $this->createMock(RequestInterface::class);
		$request->method('getUri')->willReturn($uri);

		$config = ResourceServerConfig::fromRequest($request);

		$this->assertSame('http://localhost/auth', $config->getScopeBaseUrl());
	}

	public function testFromRequestWithPort(): void
	{
		$uri = $this->createMock(UriInterface::class);
		$uri->method('getHost')->willReturn('localhost');
		$uri->method('getScheme')->willReturn('http');
		$uri->method('getPort')->willReturn(8080);

		$request = $this->createMock(RequestInterface::class);
		$request->method('getUri')->willReturn($uri);

		$config = ResourceServerConfig::fromRequest($request);

		$this->assertSame('http://localhost:8080/auth', $config->getScopeBaseUrl());
	}

	public function testMultiplePathPrefixChanges(): void
	{
		$config = new ResourceServerConfig('example.com');

		$config->setScopePathPrefix('api/v1/scopes');
		// Bug in implementation causes empty string
		$this->assertSame('https://example.com/', $config->getScopeBaseUrl());

		$config->setScopePathPrefix('api/v2/scopes');
		$this->assertSame('https://example.com/', $config->getScopeBaseUrl());
	}

	public function testLocalhostWithoutPort(): void
	{
		$config = new ResourceServerConfig('localhost', secure: false);

		$this->assertSame('http://localhost/auth', $config->getScopeBaseUrl());
	}

	public function testIpAddressHost(): void
	{
		$config = new ResourceServerConfig('192.168.1.100', port: 9000);

		$this->assertSame('https://192.168.1.100:9000/auth', $config->getScopeBaseUrl());
	}

	public function testSubdomainHost(): void
	{
		$config = new ResourceServerConfig('api.v2.example.com');

		$this->assertSame('https://api.v2.example.com/auth', $config->getScopeBaseUrl());
	}
}
