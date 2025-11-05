<?php
declare(strict_types=1);

namespace Nalgoo\Common\Tests\Unit\Infrastructure\Clock;

use Nalgoo\Common\Infrastructure\Clock\ClockService;
use PHPUnit\Framework\TestCase;

final class ClockServiceTest extends TestCase
{
	private ClockService $clockService;

	protected function setUp(): void
	{
		$this->clockService = new ClockService();
	}

	public function testGetCurrentTimeReturnsDateTimeImmutable(): void
	{
		$time = $this->clockService->getCurrentTime();

		$this->assertInstanceOf(\DateTimeImmutable::class, $time);
	}

	public function testGetCurrentTimeReturnsCurrentTime(): void
	{
		$before = new \DateTimeImmutable();
		$time = $this->clockService->getCurrentTime();
		$after = new \DateTimeImmutable();

		$this->assertGreaterThanOrEqual($before->getTimestamp(), $time->getTimestamp());
		$this->assertLessThanOrEqual($after->getTimestamp(), $time->getTimestamp());
	}

	public function testGetTimeMinutesAgoReturnsDateTimeImmutable(): void
	{
		$time = $this->clockService->getTimeMinutesAgo(10);

		$this->assertInstanceOf(\DateTimeImmutable::class, $time);
	}

	public function testGetTimeMinutesAgoReturnsCorrectTime(): void
	{
		$now = new \DateTimeImmutable();
		$time = $this->clockService->getTimeMinutesAgo(10);

		$diff = $now->getTimestamp() - $time->getTimestamp();

		// Should be approximately 10 minutes (600 seconds), allow 1 second tolerance
		$this->assertGreaterThanOrEqual(599, $diff);
		$this->assertLessThanOrEqual(601, $diff);
	}

	public function testGetTimeMinutesAheadReturnsDateTimeImmutable(): void
	{
		$time = $this->clockService->getTimeMinutesAhead(10);

		$this->assertInstanceOf(\DateTimeImmutable::class, $time);
	}

	public function testGetTimeMinutesAheadReturnsCorrectTime(): void
	{
		$now = new \DateTimeImmutable();
		$time = $this->clockService->getTimeMinutesAhead(10);

		$diff = $time->getTimestamp() - $now->getTimestamp();

		// Should be approximately 10 minutes (600 seconds), allow 1 second tolerance
		$this->assertGreaterThanOrEqual(599, $diff);
		$this->assertLessThanOrEqual(601, $diff);
	}

	public function testGetTimeMinutesAgoWithZero(): void
	{
		$now = new \DateTimeImmutable();
		$time = $this->clockService->getTimeMinutesAgo(0);

		$diff = abs($now->getTimestamp() - $time->getTimestamp());

		// Should be very close to current time (within 1 second)
		$this->assertLessThanOrEqual(1, $diff);
	}

	public function testGetTimeMinutesAheadWithZero(): void
	{
		$now = new \DateTimeImmutable();
		$time = $this->clockService->getTimeMinutesAhead(0);

		$diff = abs($now->getTimestamp() - $time->getTimestamp());

		// Should be very close to current time (within 1 second)
		$this->assertLessThanOrEqual(1, $diff);
	}

	public function testGetTimeMinutesAgoWithLargeValue(): void
	{
		$now = new \DateTimeImmutable();
		$time = $this->clockService->getTimeMinutesAgo(1440); // 24 hours

		$diff = $now->getTimestamp() - $time->getTimestamp();
		$expectedSeconds = 1440 * 60; // 86400 seconds

		$this->assertGreaterThanOrEqual($expectedSeconds - 1, $diff);
		$this->assertLessThanOrEqual($expectedSeconds + 1, $diff);
	}

	public function testGetTimeMinutesAheadWithLargeValue(): void
	{
		$now = new \DateTimeImmutable();
		$time = $this->clockService->getTimeMinutesAhead(1440); // 24 hours

		$diff = $time->getTimestamp() - $now->getTimestamp();
		$expectedSeconds = 1440 * 60; // 86400 seconds

		$this->assertGreaterThanOrEqual($expectedSeconds - 1, $diff);
		$this->assertLessThanOrEqual($expectedSeconds + 1, $diff);
	}

	public function testMultipleCallsReturnDifferentTimes(): void
	{
		$time1 = $this->clockService->getCurrentTime();
		usleep(1000); // Sleep for 1 millisecond
		$time2 = $this->clockService->getCurrentTime();

		// Times should be different (even if by microseconds)
		$this->assertNotEquals($time1->format('U.u'), $time2->format('U.u'));
	}

	public function testGetTimeMinutesAgoIsBeforeCurrent(): void
	{
		$now = $this->clockService->getCurrentTime();
		$past = $this->clockService->getTimeMinutesAgo(5);

		$this->assertLessThan($now->getTimestamp(), $past->getTimestamp());
	}

	public function testGetTimeMinutesAheadIsAfterCurrent(): void
	{
		$now = $this->clockService->getCurrentTime();
		$future = $this->clockService->getTimeMinutesAhead(5);

		$this->assertGreaterThan($now->getTimestamp(), $future->getTimestamp());
	}

	public function testGetTimeMinutesAgoWith60Minutes(): void
	{
		$now = new \DateTimeImmutable();
		$time = $this->clockService->getTimeMinutesAgo(60);

		$diff = $now->getTimestamp() - $time->getTimestamp();

		// Should be approximately 1 hour (3600 seconds)
		$this->assertGreaterThanOrEqual(3599, $diff);
		$this->assertLessThanOrEqual(3601, $diff);
	}

	public function testGetTimeMinutesAheadWith60Minutes(): void
	{
		$now = new \DateTimeImmutable();
		$time = $this->clockService->getTimeMinutesAhead(60);

		$diff = $time->getTimestamp() - $now->getTimestamp();

		// Should be approximately 1 hour (3600 seconds)
		$this->assertGreaterThanOrEqual(3599, $diff);
		$this->assertLessThanOrEqual(3601, $diff);
	}

	public function testDateTimeImmutableIsActuallyImmutable(): void
	{
		$time = $this->clockService->getCurrentTime();
		$original = $time->format('Y-m-d H:i:s');

		$modified = $time->modify('+1 day');

		// Original should be unchanged
		$this->assertSame($original, $time->format('Y-m-d H:i:s'));
		// Modified should be different
		$this->assertNotSame($original, $modified->format('Y-m-d H:i:s'));
	}
}
