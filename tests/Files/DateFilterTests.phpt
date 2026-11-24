<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Tests\Files;

use Contributte\Datagrid\DataSource\IDataSource;
use Contributte\Datagrid\Filter\FilterDate;
use Contributte\Datagrid\Filter\FilterDateRange;
use DateTime;
use DateTimeZone;
use Tester\Assert;

trait DateFilterTests
{

	public function testDateFilterCalendarDays(): void
	{
		foreach (['2024-02-29', '2024-03-31', '2024-10-27', '2024-12-31'] as $day) {
			$date = new DateTime($day, new DateTimeZone('Europe/Prague'));
			$previous = (clone $date)->modify('-1 day')->format('Y-m-d');
			$next = (clone $date)->modify('+1 day')->format('Y-m-d');
			$rows = [
				['id' => 1, 'created' => $previous . ' 23:59:59.999999'],
				['id' => 2, 'created' => $day . ' 00:00:00'],
				['id' => 3, 'created' => $day . ' 12:30:00'],
				['id' => 4, 'created' => $day . ' 23:59:59.999999'],
				['id' => 5, 'created' => $next . ' 00:00:00'],
			];
			$grid = (new TestingDatagridFactory())->createTestingDatagrid();
			$filter = new FilterDate($grid, 'created', 'Created', 'created');
			$filter->setFormat('Y-m-d', 'yyyy-mm-dd');
			$filter->setValue($day);
			$source = $this->createDateSource($rows);
			$source->filter([$filter]);
			Assert::same([2, 3, 4], $this->dateResultIds($source));

			$from = new DateTime($day . ' 14:15:16.123456', new DateTimeZone('Europe/Prague'));
			$to = new DateTime($day . ' 20:21:22.654321', new DateTimeZone('Europe/Prague'));
			foreach ([
				[['from' => $day, 'to' => $day], [2, 3, 4]],
				[['from' => $day, 'to' => ''], [2, 3, 4, 5]],
				[['from' => '', 'to' => $day], [1, 2, 3, 4]],
				[['from' => '', 'to' => ''], [1, 2, 3, 4, 5]],
				[['from' => 'invalid', 'to' => $day], [1, 2, 3, 4]],
				[['from' => $day, 'to' => 'invalid'], [2, 3, 4, 5]],
				[['from' => $next, 'to' => $previous], []],
				[['from' => $from, 'to' => $to], [2, 3, 4]],
			] as [$bounds, $expected]) {
				$range = new FilterDateRange($grid, 'created', 'Created', 'created', 'To');
				$range->setFormat('Y-m-d', 'yyyy-mm-dd');
				$range->setValue($bounds);
				$source = $this->createDateSource($rows);
				$source->filter([$range]);
				Assert::same($expected, $this->dateResultIds($source));
			}

			Assert::same($day . ' 14:15:16.123456 Europe/Prague', $from->format('Y-m-d H:i:s.u e'));
			Assert::same($day . ' 20:21:22.654321 Europe/Prague', $to->format('Y-m-d H:i:s.u e'));
			$filter->setValue($from);
			$source = $this->createDateSource($rows);
			$source->filter([$filter]);
			Assert::same([2, 3, 4], $this->dateResultIds($source));
			Assert::same($day . ' 14:15:16.123456 Europe/Prague', $from->format('Y-m-d H:i:s.u e'));
		}
	}

	abstract protected function createDateSource(array $rows): IDataSource;

	protected function assertMidnightTransitionTextBoundaries(): void
	{
		$grid = (new TestingDatagridFactory())->createTestingDatagrid();
		foreach (['2024-04-25', '2024-04-26'] as $day) {
			$input = new DateTime($day . ' 12:00:00', new DateTimeZone('Africa/Cairo'));
			$next = (clone $input)->modify('+1 day')->format('Y-m-d');
			$rows = [
				['id' => 1, 'created' => $day . ' 23:59:59.999999'],
				['id' => 2, 'created' => $next . ' 00:30:00'],
			];
			$date = new FilterDate($grid, 'created', 'Date', 'created');
			$date->setValue($input);
			$range = new FilterDateRange($grid, 'created', 'Date', 'created', 'To');
			$range->setValue(['from' => '', 'to' => $input]);
			foreach ([$date, $range] as $filter) {
				$source = $this->createDateSource($rows);
				$source->filter([$filter]);
				Assert::same([1], $this->dateResultIds($source));
			}
		}
	}

	private function dateResultIds(IDataSource $source): array
	{
		$ids = array_map(static fn ($row): int => (int) (is_array($row) || $row instanceof \ArrayAccess ? $row['id'] : $row->id), $source->getData());
		sort($ids);

		return $ids;
	}

}
