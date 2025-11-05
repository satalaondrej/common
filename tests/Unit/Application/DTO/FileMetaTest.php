<?php
declare(strict_types=1);

namespace Nalgoo\Common\Tests\Unit\Application\DTO;

use Nalgoo\Common\Application\DTO\FileMeta;
use PHPUnit\Framework\TestCase;

final class FileMetaTest extends TestCase
{
	public function testConstructorAndGetters(): void
	{
		$meta = new FileMeta('test.pdf', 1024, 'application/pdf');

		$this->assertSame('test.pdf', $meta->getName());
		$this->assertSame(1024, $meta->getSize());
		$this->assertSame('application/pdf', $meta->getContentType());
	}

	public function testZeroSizeFile(): void
	{
		$meta = new FileMeta('empty.txt', 0, 'text/plain');

		$this->assertSame(0, $meta->getSize());
	}

	public function testLargeFile(): void
	{
		$largeSize = 1024 * 1024 * 100; // 100 MB
		$meta = new FileMeta('large.zip', $largeSize, 'application/zip');

		$this->assertSame($largeSize, $meta->getSize());
	}

	public function testVariousContentTypes(): void
	{
		$types = [
			['image.jpg', 'image/jpeg'],
			['document.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
			['data.json', 'application/json'],
			['style.css', 'text/css'],
			['script.js', 'application/javascript'],
		];

		foreach ($types as [$name, $contentType]) {
			$meta = new FileMeta($name, 100, $contentType);

			$this->assertSame($name, $meta->getName());
			$this->assertSame($contentType, $meta->getContentType());
		}
	}

	public function testFileNameWithPath(): void
	{
		$meta = new FileMeta('/path/to/file.txt', 500, 'text/plain');

		$this->assertSame('/path/to/file.txt', $meta->getName());
	}

	public function testFileNameWithSpecialCharacters(): void
	{
		$meta = new FileMeta('file (1) - copy.txt', 256, 'text/plain');

		$this->assertSame('file (1) - copy.txt', $meta->getName());
	}

	public function testFileNameWithUnicode(): void
	{
		$meta = new FileMeta('документ.pdf', 1000, 'application/pdf');

		$this->assertSame('документ.pdf', $meta->getName());
	}
}
