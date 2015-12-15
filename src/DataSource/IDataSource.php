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
	 * @return int
	 */
	public function getCount();


	/**
	 * @return array
	 */
	public function getData();


	/**
	 * @param array $filters
	 * @return void
	 */
	public function filter(array $filters);


	/**
	 * @param array $filter
	 * @return void
	 */
	public function filterOne(array $filter);


	/**
	 * @param int $offset
	 * @param int $limit
	 * @return void
	 */
	public function limit($offset, $limit);


	/**
	 * @param array $sorting
	 * @return void
	 */
	public function sort(array $sorting);

}
