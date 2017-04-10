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
	 * @var int
	 */
	protected $dataType;

	/**
	 * @param string $column
	 * @param int $dataType
	 */
	public function __construct($column, $dataType = IAggregationFunction::DATA_TYPE_PAGINATED)
	{
		$this->column = $column;
		$this->dataType = $dataType;
	}


	/**
	 * @return bool
	 */
	public function getFilterDataType()
	{
		return $this->dataType;
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
		if ($data_source instanceof \Doctrine\ORM\QueryBuilder) {
			$column = \Nette\Utils\Strings::contains($this->column, '.')
				? $this->column 
				: current($data_source->getRootAliases()).'.'.$this->column;
			$this->result = $data_source
				->select(sprintf('SUM(%s)', $column))
				->getQuery()
				->getSingleScalarResult();
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
