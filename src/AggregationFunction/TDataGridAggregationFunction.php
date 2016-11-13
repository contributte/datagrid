<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\AggregationFunction;

use Ublaboo\DataGrid\Exception\DataGridException;
use Ublaboo\DataGrid\DataModel;
use Ublaboo\DataGrid\DataSource\IDataSource;

trait TDataGridAggregationFunction
{

	/**
	 * @var IAggregationFunction[]
	 */
	private $aggregationFunctions = [];


	/**
	 * @param string               $key
	 * @param IAggregationFunction $aggregationFunction
	 * @return static
	 */
	public function addAggregationFunction($key, IAggregationFunction $aggregationFunction)
	{
		if (!($this->dataModel instanceof DataModel)) {
			throw new DataGridException('You have to set a data source first.');
		}

		if (isset($this->aggregationFunctions[$key])) {
			throw new DataGridException(
				"There is already a AggregationFunction defined on column {$key}"
			);
		}

		$this->aggregationFunctions[$key] = $aggregationFunction;

		return $this;
	}


	/**
	 * @param  IDataSource $dataSource
	 * @return void
	 */
	public function beforeDataModelFilter(IDataSource $dataSource)
	{
		foreach ($this->aggregationFunctions as $aggregationFunction) {
			if ($aggregationFunction->getFilterDataType() === IAggregationFunction::DATA_TYPE_ALL) {
				$dataSource->processAggregation([$aggregationFunction, 'processDataSource']);
			}
		}
	}


	/**
	 * @param  IDataSource $dataSource
	 * @return void
	 */
	public function afterDataModelFilter(IDataSource $dataSource)
	{
		foreach ($this->aggregationFunctions as $aggregationFunction) {
			if ($aggregationFunction->getFilterDataType() === IAggregationFunction::DATA_TYPE_FILTERED) {
				$dataSource->processAggregation([$aggregationFunction, 'processDataSource']);
			}
		}
	}


	/**
	 * @param  IDataSource $dataSource
	 * @return void
	 */
	public function afterDataModelPaginated(IDataSource $dataSource)
	{
		foreach ($this->aggregationFunctions as $aggregationFunction) {
			if ($aggregationFunction->getFilterDataType() === IAggregationFunction::DATA_TYPE_PAGINATED) {
				$dataSource->processAggregation([$aggregationFunction, 'processDataSource']);
			}
		}
	}


	/**
	 * @return bool
	 */
	public function hasSomeAggregationFunction()
	{
		return !empty($this->aggregationFunctions);
	}


	/**
	 * @return IAggregationFunction[]
	 */
	public function getAggregationFunctions()
	{
		return $this->aggregationFunctions;
	}

}
