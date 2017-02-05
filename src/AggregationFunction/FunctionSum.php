<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\AggregationFunction;

class FunctionSum implements IAggregationFunction
{

	/**
	 * @var string
	 */
	protected $column;

	/**
	 * @var int
	 */
	protected $result = 0;


	/**
	 * @param string $column
	 */
	public function __construct($column)
	{
		$this->column = $column;
	}


	/**
	 * @return bool
	 */
	public function getFilterDataType()
	{
		return IAggregationFunction::DATA_TYPE_PAGINATED;
	}


	/**
	 * @param  mixed $data_source
	 * @return void
	 */
	public function processDataSource($data_source)
	{
		if ($data_source instanceof \DibiFluent) {
			$connection = $data_source->getConnection();
			$this->result = $connection->select('SUM(%n) AS sum', $this->column)
				->from($data_source, 's')
				->fetch()
				->sum;
		}
	}


	/**
	 * @return int
	 */
	public function renderResult()
	{
		return $this->result;
	}

}
