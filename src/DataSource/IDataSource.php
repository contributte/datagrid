<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\DataSource;

interface IDataSource
{

	/**
	 * Get count of data
	 * @return int
	 */
	public function getCount();


	/**
	 * Get the data
	 * @return array
	 */
	public function getData();


	/**
	 * Filter data
	 * @param array $filters
	 * @return void
	 */
	public function filter(array $filters);


	/**
	 * Filter data - get one row
	 * @param array $filter
	 * @return void
	 */
	public function filterOne(array $filter);


	/**
	 * Apply limit and offet on data
	 * @param int $offset
	 * @param int $limit
	 * @return void
	 */
	public function limit($offset, $limit);


	/**
	 * Order data
	 * @param array $sorting
	 * @return void
	 */
	public function sort(array $sorting);

}
