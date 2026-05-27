<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Tests\Cases\Utils;

use Contributte\Datagrid\Exception\DatagridDateTimeHelperException;
use Contributte\Datagrid\Utils\DateTimeHelper;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';

final class DateTimeHelperTest extends TestCase
{

	public function testReturnsDateTimeInstanceUnchanged(): void
	{
		$date = new DateTime('2024-01-02 03:04:05', new DateTimeZone('Europe/Prague'));

		Assert::same($date, DateTimeHelper::tryConvertToDateTime($date));
	}

	public function testConvertsDateTimeImmutable(): void
	{
		$value = new DateTimeImmutable('2024-01-02 03:04:05', new DateTimeZone('Europe/Prague'));
		$date = DateTimeHelper::tryConvertToDateTime($value);

		Assert::type(DateTime::class, $date);
		Assert::same('2024-01-02 03:04:05 Europe/Prague', $date->format('Y-m-d H:i:s e'));
	}

	public function testParsesDefaultFormats(): void
	{
		Assert::same('2024-01-02 03:04:05.123456', DateTimeHelper::fromString('2024-01-02 03:04:05.123456')->format('Y-m-d H:i:s.u'));
		Assert::same('2024-01-02 03:04:05', DateTimeHelper::fromString('2024-01-02 03:04:05')->format('Y-m-d H:i:s'));
		Assert::same('2024-01-02', DateTimeHelper::fromString('2024-01-02')->format('Y-m-d'));
		Assert::same('2024-01-02 03:04:05', DateTimeHelper::fromString('2. 1. 2024 3:04:05')->format('Y-m-d H:i:s'));
		Assert::same(123456, DateTimeHelper::fromString('123456')->getTimestamp());
	}

	public function testCustomFormatHasPriority(): void
	{
		$date = DateTimeHelper::tryConvertToDate('31/12/2024', ['d/m/Y']);

		Assert::same('2024-12-31', $date->format('Y-m-d'));
	}

	public function testInvalidValueThrowsException(): void
	{
		Assert::exception(
			fn () => DateTimeHelper::fromString('not a date'),
			DatagridDateTimeHelperException::class
		);
	}

}


(new DateTimeHelperTest())->run();
