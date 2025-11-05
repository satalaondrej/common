<?php
declare(strict_types=1);

namespace Nalgoo\Common\Tests\Unit\Infrastructure\Persistence\DoctrineTypes;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Nalgoo\Common\Infrastructure\Persistence\DoctrineTypes\IPAddressType;
use PHPUnit\Framework\TestCase;

final class IPAddressTypeTest extends TestCase
{
	private IPAddressType $type;
	private AbstractPlatform $platform;

	protected function setUp(): void
	{
		$this->type = new IPAddressType();
		$this->platform = $this->createMock(AbstractPlatform::class);
	}

	public function testGetName(): void
	{
		$this->assertSame('ip_address', $this->type->getName());
	}

	public function testConvertToDatabaseValueWithIPv4(): void
	{
		$ip = '192.168.1.1';
		$result = $this->type->convertToDatabaseValue($ip, $this->platform);

		$this->assertIsString($result);
		$this->assertSame(inet_pton($ip), $result);
	}

	public function testConvertToDatabaseValueWithIPv6(): void
	{
		$ip = '2001:0db8:85a3:0000:0000:8a2e:0370:7334';
		$result = $this->type->convertToDatabaseValue($ip, $this->platform);

		$this->assertIsString($result);
		$this->assertSame(inet_pton($ip), $result);
	}

	public function testConvertToDatabaseValueWithNull(): void
	{
		$result = $this->type->convertToDatabaseValue(null, $this->platform);

		$this->assertNull($result);
	}

	public function testConvertToPHPValueWithIPv4Binary(): void
	{
		$ip = '10.0.0.1';
		$binary = inet_pton($ip);
		$result = $this->type->convertToPHPValue($binary, $this->platform);

		$this->assertSame($ip, $result);
	}

	public function testConvertToPHPValueWithIPv6Binary(): void
	{
		$ip = '::1';
		$binary = inet_pton($ip);
		$result = $this->type->convertToPHPValue($binary, $this->platform);

		$this->assertSame($ip, $result);
	}

	public function testConvertToPHPValueWithNull(): void
	{
		$result = $this->type->convertToPHPValue(null, $this->platform);

		$this->assertNull($result);
	}

	public function testRoundTripIPv4(): void
	{
		$original = '172.16.0.1';

		$database = $this->type->convertToDatabaseValue($original, $this->platform);
		$this->assertIsString($database);

		$php = $this->type->convertToPHPValue($database, $this->platform);
		$this->assertSame($original, $php);
	}

	public function testRoundTripIPv6(): void
	{
		$original = 'fe80::1';

		$database = $this->type->convertToDatabaseValue($original, $this->platform);
		$this->assertIsString($database);

		$php = $this->type->convertToPHPValue($database, $this->platform);
		$this->assertSame($original, $php);
	}

	public function testRoundTripIPv6FullAddress(): void
	{
		$original = '2001:0db8:85a3:0000:0000:8a2e:0370:7334';

		$database = $this->type->convertToDatabaseValue($original, $this->platform);
		$php = $this->type->convertToPHPValue($database, $this->platform);

		// PHP normalizes IPv6 addresses
		$this->assertSame(inet_ntop(inet_pton($original)), $php);
	}

	public function testLocalhostIPv4(): void
	{
		$ip = '127.0.0.1';
		$database = $this->type->convertToDatabaseValue($ip, $this->platform);
		$php = $this->type->convertToPHPValue($database, $this->platform);

		$this->assertSame($ip, $php);
	}

	public function testLocalhostIPv6(): void
	{
		$ip = '::1';
		$database = $this->type->convertToDatabaseValue($ip, $this->platform);
		$php = $this->type->convertToPHPValue($database, $this->platform);

		$this->assertSame($ip, $php);
	}

	public function testPrivateIPv4Range(): void
	{
		$ips = [
			'10.0.0.0',
			'10.255.255.255',
			'172.16.0.0',
			'172.31.255.255',
			'192.168.0.0',
			'192.168.255.255',
		];

		foreach ($ips as $ip) {
			$database = $this->type->convertToDatabaseValue($ip, $this->platform);
			$php = $this->type->convertToPHPValue($database, $this->platform);

			$this->assertSame($ip, $php, "Failed for IP: $ip");
		}
	}

	public function testIPv6CompressedNotation(): void
	{
		$ip = '2001:db8::8a2e:370:7334';
		$database = $this->type->convertToDatabaseValue($ip, $this->platform);
		$php = $this->type->convertToPHPValue($database, $this->platform);

		$this->assertSame($ip, $php);
	}

	public function testZeroIPv4(): void
	{
		$ip = '0.0.0.0';
		$database = $this->type->convertToDatabaseValue($ip, $this->platform);
		$php = $this->type->convertToPHPValue($database, $this->platform);

		$this->assertSame($ip, $php);
	}

	public function testBroadcastIPv4(): void
	{
		$ip = '255.255.255.255';
		$database = $this->type->convertToDatabaseValue($ip, $this->platform);
		$php = $this->type->convertToPHPValue($database, $this->platform);

		$this->assertSame($ip, $php);
	}
}
