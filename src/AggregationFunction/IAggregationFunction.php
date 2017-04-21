<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\AggregationFunction;

interface IAggregationFunction
{
	const DATA_TYPE_ALL = 'data_type_all';
	const DATA_TYPE_FILTERED = 'data_type_filtered';
	const DATA_TYPE_PAGINATED = 'data_type_paginated';


	/**
	 * @return string
	 */
	public function getFilterDataType();

	/**
	 * @param  mixed  $dataSource
	 * @return void
	 */
	public function processDataSource($dataSource);

	/**
	 * @return mixed
	 */
	public function renderResult();
}
