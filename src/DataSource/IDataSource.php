<?php declare(strict_types = 1);

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\DataSource;

use Ublaboo\DataGrid\Utils\Sorting;

interface IDataSource
{

	/**
	 * Get count of data
	 *
	 * @return int
	 */
	public function getCount(): int;

	/**
	 * Get the data
	 *
	 * @return array
	 */
	public function getData(): array;

	/**
	 * Filter data
	 *
	 * @param array $filters
	 * @return static
	 */
	public function filter(array $filters);

	/**
	 * Filter data - get one row
	 *
	 * @param array $filter
	 * @return static
	 */
	public function filterOne(array $filter);

	/**
	 * Apply limit and offset on data
	 *
	 * @param int $offset
	 * @param int $limit
	 * @return static
	 */
	public function limit(int $offset, int $limit);

	/**
	 * Sort data
	 *
	 * @param Sorting $sorting
	 * @return static
	 */
	public function sort(Sorting $sorting);

}
