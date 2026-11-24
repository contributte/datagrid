<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Tests\Cases\Utils;

use Contributte\Datagrid\Utils\DateTimeHelper;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';

final class DateTimeBoundaryTest extends TestCase
{

	public function testNextDayStartsAtFirstValidTime(): void
	{
		foreach ([
			['Africa/Cairo', '2024-04-25', '2024-04-26 01:00:00'],
			['Africa/Cairo', '2024-04-26', '2024-04-27 00:00:00'],
			['Europe/Prague', '2024-03-31', '2024-04-01 00:00:00'],
			['Europe/Prague', '2024-10-27', '2024-10-28 00:00:00'],
		] as [$zone, $day, $expected]) {
			$input = new DateTime($day . ' 12:34:56.123456', new DateTimeZone($zone));
			foreach ([$input, DateTimeImmutable::createFromMutable($input)] as $date) {
				$next = DateTimeHelper::getNextDay($date);
				Assert::same($expected, $next->format('Y-m-d H:i:s'));
				Assert::same('000000', $next->format('u'));
				Assert::same($zone, $next->getTimezone()->getName());
				Assert::same($day . ' 12:34:56.123456', $date->format('Y-m-d H:i:s.u'));
			}
		}
	}

}

(new DateTimeBoundaryTest())->run();
