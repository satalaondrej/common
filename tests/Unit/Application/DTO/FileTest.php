<?php
declare(strict_types=1);

namespace Nalgoo\Common\Tests\Unit\Application\DTO;

use Nalgoo\Common\Application\DTO\File;
use Nalgoo\Common\Application\DTO\FileMeta;
use PHPUnit\Framework\TestCase;

final class FileTest extends TestCase
{
	public function testConstructorAndGetters(): void
	{
		$meta = new FileMeta('test.txt', 11, 'text/plain');
		$file = new File($meta, 'Hello World');

		$this->assertSame($meta, $file->getMeta());
		$this->assertSame('Hello World', $file->getContents());
	}

	public function testGetChecksum(): void
	{
		$meta = new FileMeta('test.txt', 11, 'text/plain');
		$file = new File($meta, 'Hello World');

		// SHA3-256 hash of "Hello World"
		$expectedChecksum = hash('sha3-256', 'Hello World');
		$this->assertSame($expectedChecksum, $file->getChecksum());
		$this->assertSame(64, strlen($file->getChecksum())); // SHA3-256 produces 64 hex chars
	}

	public function testEmptyFileChecksum(): void
	{
		$meta = new FileMeta('empty.txt', 0, 'text/plain');
		$file = new File($meta, '');

		$expectedChecksum = hash('sha3-256', '');
		$this->assertSame($expectedChecksum, $file->getChecksum());
	}

	public function testDifferentContentsDifferentChecksums(): void
	{
		$meta1 = new FileMeta('file1.txt', 5, 'text/plain');
		$file1 = new File($meta1, 'test1');

		$meta2 = new FileMeta('file2.txt', 5, 'text/plain');
		$file2 = new File($meta2, 'test2');

		$this->assertNotSame($file1->getChecksum(), $file2->getChecksum());
	}

	public function testSameContentsSameChecksums(): void
	{
		$meta1 = new FileMeta('file1.txt', 4, 'text/plain');
		$file1 = new File($meta1, 'test');

		$meta2 = new FileMeta('file2.txt', 4, 'text/plain');
		$file2 = new File($meta2, 'test');

		$this->assertSame($file1->getChecksum(), $file2->getChecksum());
	}

	public function testBinaryContent(): void
	{
		$binaryContent = "\x00\x01\x02\x03\xFF\xFE\xFD";
		$meta = new FileMeta('binary.dat', strlen($binaryContent), 'application/octet-stream');
		$file = new File($meta, $binaryContent);

		$this->assertSame($binaryContent, $file->getContents());
		$this->assertSame(hash('sha3-256', $binaryContent), $file->getChecksum());
	}

	public function testLargeTextContent(): void
	{
		$content = str_repeat('Lorem ipsum dolor sit amet. ', 1000);
		$meta = new FileMeta('large.txt', strlen($content), 'text/plain');
		$file = new File($meta, $content);

		$this->assertSame($content, $file->getContents());
		$this->assertSame(hash('sha3-256', $content), $file->getChecksum());
	}

	public function testMultilineContent(): void
	{
		$content = "Line 1\nLine 2\r\nLine 3\rLine 4";
		$meta = new FileMeta('multiline.txt', strlen($content), 'text/plain');
		$file = new File($meta, $content);

		$this->assertSame($content, $file->getContents());
	}

	public function testUnicodeContent(): void
	{
		$content = 'Привет мир! 你好世界! مرحبا بالعالم!';
		$meta = new FileMeta('unicode.txt', strlen($content), 'text/plain; charset=utf-8');
		$file = new File($meta, $content);

		$this->assertSame($content, $file->getContents());
		$this->assertSame(hash('sha3-256', $content), $file->getChecksum());
	}

	public function testJsonContent(): void
	{
		$json = '{"name": "John", "age": 30, "active": true}';
		$meta = new FileMeta('data.json', strlen($json), 'application/json');
		$file = new File($meta, $json);

		$this->assertSame($json, $file->getContents());
		$this->assertSame(hash('sha3-256', $json), $file->getChecksum());
	}
}
