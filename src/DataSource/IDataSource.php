<?php

declare(strict_types=1);

namespace Ublaboo\DataGrid\DataSource;

use Ublaboo\DataGrid\Filter\Filter;
use Ublaboo\DataGrid\Utils\Sorting;

interface IDataSource
{

	/**
	 * Get count of data
	 */
	public function getCount(): int;

	/**
	 * Get the data
	 */
	public function getData(): iterable;

	/**
	 * Filter data
	 *
	 * @param array<Filter> $filters
	 */
	public function filter(array $filters): void;

	/**
	 * Filter data - get one row
	 *
	 * @return static
	 */
	public function filterOne(array $condition): self;

	/**
	 * Apply limit and offset on data
	 *
	 * @phpstan-param positive-int|0 $offset
	 * @phpstan-param positive-int|0 $limit
	 * @return static
	 */
	public function limit(int $offset, int $limit): self;

	/**
	 * Sort data
	 *
	 * @return static
	 */
	public function sort(Sorting $sorting): self;

}
