<?php

namespace Ublaboo\DataGrid\Column;

class ColumnAggregationFunction
{
	/**
	 * @var string
	 */
	protected $column;

	/**
	 * @var string
	 */
	protected $aggregation_type;

	public static $aggregation_type_sum = 'sum';

	public static $aggregation_type_avg = 'avg';

	public static $aggregation_type_min = 'min';

	public static $aggregation_type_max = 'max';

	/**
	 * ColumnSummary constructor.
	 * @param $aggregation_type
	 * @param string $column
	 */
	public function __construct($aggregation_type, $column)
	{
		$this->aggregation_type = $aggregation_type;
		$this->column = $column;
	}


	/**
	 * @return string
	 */
	public function getAggregationType()
	{
		return $this->aggregation_type;
	}


	/**
	 * @param string $value use ColumnAggregationFunction::$aggregation_type_...
	 * @return ColumnSummary
	 */
	public function setAggregationType($value)
	{
		$this->aggregation_type = $value;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getColumn()
	{
		return $this->column;
	}
}
