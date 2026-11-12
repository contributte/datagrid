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

	public function testPartialFilterDatesRespectFormatAndReferenceYear(): void
	{
		$reference = new DateTimeImmutable('2028-01-01 00:15:00', new DateTimeZone('Pacific/Kiritimati'));
		foreach ([
			['12.5.', 'j. n. Y', '2028-05-12'],
			[' 12. 5. ', 'j. n. Y', '2028-05-12'],
			['12.5', 'd.m.Y', '2028-05-12'],
			['12/5', 'd/m/Y', '2028-05-12'],
			['12/5/', 'm/d/Y', '2028-12-05'],
			['12-5', 'd-m-y', '2028-05-12'],
			['12 5', 'j n Y', '2028-05-12'],
			['29.2.', 'j. n. Y', '2028-02-29'],
		] as [$input, $format, $expected]) {
			$date = DateTimeHelper::tryConvertToFilterDate($input, $format, $reference);
			Assert::same($expected . ' 00:00:00 Pacific/Kiritimati', $date->format('Y-m-d H:i:s e'));
		}

		Assert::same('2028-01-01 00:15:00', $reference->format('Y-m-d H:i:s'));
	}

	public function testInvalidPartialFilterDatesDoNotRollOver(): void
	{
		$reference = new DateTimeImmutable('2026-01-01');
		foreach (['29.2.', '31.2.', '0.5.', '12.13.'] as $input) {
			Assert::exception(
				fn () => DateTimeHelper::tryConvertToFilterDate($input, 'j. n. Y', $reference),
				DatagridDateTimeHelperException::class
			);
		}
	}

	public function testConfiguredFormatsAndNonFilterDateParsing(): void
	{
		Assert::same('2024-05-12', DateTimeHelper::tryConvertToFilterDate('12/5/2024', 'd/m/Y')->format('Y-m-d'));
		Assert::same('2024-12-05', DateTimeHelper::tryConvertToFilterDate('12/5/2024', 'm/d/Y')->format('Y-m-d'));
		Assert::same('2024-05-12', DateTimeHelper::tryConvertToFilterDate('2024-05-12', 'Y-m-d')->format('Y-m-d'));
		Assert::same('12:05', DateTimeHelper::tryConvertToFilterDate('12.05', 'H.i')->format('H:i'));
		Assert::same('12:05', DateTimeHelper::fromString('12.5.')->format('H:i'));
		$date = new DateTime('2024-05-12 03:04:05');
		Assert::same($date, DateTimeHelper::tryConvertToFilterDate($date, 'j. n. Y'));
		$immutable = new DateTimeImmutable('2024-05-12 03:04:05', new DateTimeZone('Pacific/Kiritimati'));
		Assert::same('2024-05-12 03:04:05 Pacific/Kiritimati', DateTimeHelper::tryConvertToFilterDate($immutable, 'j. n. Y')->format('Y-m-d H:i:s e'));
		Assert::same(123456, DateTimeHelper::tryConvertToFilterDate(123456, 'U')->getTimestamp());
	}

	public function testFullFilterDatesUseOnlyTheConfiguredFormat(): void
	{
		foreach ([
			['2. 1. 2025', 'j. n. Y', '2025-01-02'],
			['2.1.2025', 'j. n. Y', '2025-01-02'],
			['29/02/2024', 'd/m/Y', '2024-02-29'],
			['02/01/2025', 'm/d/Y', '2025-02-01'],
			['2025-01-02', 'Y-m-d', '2025-01-02'],
			['12.5.25', 'j. n. y', '2025-05-12'],
		] as [$input, $format, $expected]) {
			Assert::same($expected . ' 00:00:00', DateTimeHelper::tryConvertToFilterDate($input, $format)->format('Y-m-d H:i:s'));
		}
	}

	public function testFilterDatesRejectFallbacksAndCalendarOverflows(): void
	{
		foreach ([
			['2/1', 'j. n. Y'],
			['2-1', 'j. n. Y'],
			['2025-01-02', 'j. n. Y'],
			['tomorrow', 'j. n. Y'],
			['1735776000', 'j. n. Y'],
			['31.2.2026', 'j. n. Y'],
			['29.2.2025', 'j. n. Y'],
			['00/01/2025', 'd/m/Y'],
			['01/13/2025', 'd/m/Y'],
			['2025-02-29', 'Y-m-d'],
			['12:65', 'H:i'],
			["2.1.2025\0", 'j. n. Y'],
			[' 2.1.2025', 'j. n. Y'],
			['2.1.2025 ', 'j. n. Y'],
		] as [$input, $format]) {
			Assert::exception(
				fn () => DateTimeHelper::tryConvertToFilterDate($input, $format),
				DatagridDateTimeHelperException::class
			);
		}
	}

	public function testOmittedYearAlwaysUsesTheCurrentYear(): void
	{
		$reference = new DateTimeImmutable('2026-01-01');
		Assert::same('2026-12-28', DateTimeHelper::tryConvertToFilterDate('28.12.', 'j. n. Y', $reference)->format('Y-m-d'));
	}

	public function testFullFilterDateUsesTheApplicationTimezone(): void
	{
		$reference = new DateTimeImmutable('2028-01-01', new DateTimeZone('Pacific/Kiritimati'));
		$date = DateTimeHelper::tryConvertToFilterDate('2.1.2025', 'j. n. Y', $reference);
		Assert::same('2025-01-02 00:00:00 Europe/Prague', $date->format('Y-m-d H:i:s e'));
	}

}


(new DateTimeHelperTest())->run();
