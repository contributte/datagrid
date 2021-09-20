<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\AggregationFunction;

interface IMultipleAggregationFunction
{
	const DATA_TYPE_ALL = IAggregationFunction::DATA_TYPE_ALL;
	const DATA_TYPE_FILTERED = IAggregationFunction::DATA_TYPE_FILTERED;
	const DATA_TYPE_PAGINATED = IAggregationFunction::DATA_TYPE_PAGINATED;

	/**
	 * @return string
	 */
	public function getFilterDataType();

	/**
	 * @param  mixed $data_source
	 * @return void
	 */
	public function processDataSource($data_source);

	/**
	 * @param  string $key
	 * @return mixed
	 */
	public function renderResult($key);
}
