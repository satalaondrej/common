<?php
declare(strict_types=1);

namespace Nalgoo\Common\Tests\Unit\Infrastructure\Url;

use Nalgoo\Common\Infrastructure\Url\QueryString;
use PHPUnit\Framework\TestCase;

final class QueryStringTest extends TestCase
{
	public function testCreateEmpty(): void
	{
		$qs = new QueryString([]);

		$this->assertSame('', (string) $qs);
	}

	public function testCreateWithParams(): void
	{
		$qs = new QueryString(['foo' => 'bar', 'baz' => 'qux']);

		$this->assertSame('foo=bar&baz=qux', (string) $qs);
	}

	public function testStaticNew(): void
	{
		$qs = QueryString::new(['foo' => 'bar']);

		$this->assertSame('foo=bar', (string) $qs);
	}

	public function testWithParam(): void
	{
		$qs = QueryString::new(['foo' => 'bar']);
		$qs2 = $qs->withParam('baz', 'qux');

		$this->assertSame('foo=bar', (string) $qs);
		$this->assertSame('foo=bar&baz=qux', (string) $qs2);
	}

	public function testWithParamOverwrite(): void
	{
		$qs = QueryString::new(['foo' => 'bar']);
		$qs2 = $qs->withParam('foo', 'updated');

		$this->assertSame('foo=bar', (string) $qs);
		$this->assertSame('foo=updated', (string) $qs2);
	}

	public function testWithParams(): void
	{
		$qs = QueryString::new(['foo' => 'bar']);
		$qs2 = $qs->withParams(['baz' => 'qux']);

		$this->assertSame('foo=bar', (string) $qs);
		$this->assertSame('baz=qux', (string) $qs2);
	}

	public function testWithSeparator(): void
	{
		$qs = QueryString::new(['foo' => 'bar'])->withSeparator();

		$this->assertSame('?foo=bar', (string) $qs);
	}

	public function testWithSeparatorEmpty(): void
	{
		$qs = QueryString::new([])->withSeparator();

		$this->assertSame('', (string) $qs);
	}

	public function testImmutability(): void
	{
		$qs1 = QueryString::new(['foo' => 'bar']);
		$qs2 = $qs1->withParam('baz', 'qux');
		$qs3 = $qs2->withSeparator();

		$this->assertNotSame($qs1, $qs2);
		$this->assertNotSame($qs2, $qs3);
		$this->assertSame('foo=bar', (string) $qs1);
		$this->assertSame('foo=bar&baz=qux', (string) $qs2);
		$this->assertSame('?foo=bar&baz=qux', (string) $qs3);
	}

	public function testSpecialCharactersEncoding(): void
	{
		$qs = QueryString::new(['key' => 'value with spaces', 'special' => 'a+b=c']);

		$this->assertStringContainsString('value%20with%20spaces', (string) $qs);
		$this->assertStringContainsString('a%2Bb%3Dc', (string) $qs);
	}

	public function testArrayValues(): void
	{
		$qs = QueryString::new(['colors' => ['red', 'green', 'blue']]);
		$result = (string) $qs;

		$this->assertStringContainsString('colors', $result);
		$this->assertStringContainsString('red', $result);
		$this->assertStringContainsString('green', $result);
		$this->assertStringContainsString('blue', $result);
	}

	public function testNumericValues(): void
	{
		$qs = QueryString::new(['page' => 1, 'limit' => 10]);

		$this->assertSame('page=1&limit=10', (string) $qs);
	}
}
