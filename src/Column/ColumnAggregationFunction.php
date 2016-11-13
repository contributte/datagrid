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

	const AGGREGATION_TYPE_SUM = 'sum';

	const AGGREGATION_TYPE_AVG = 'avg';

	const AGGREGATION_TYPE_MIN = 'min';

	const AGGREGATION_TYPE_MAX = 'max';

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
	 * @param string $value use ColumnAggregationFunction::AGGREGATION_TYPE_...
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
