<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\AggregationFunction;

use Ublaboo\DataGrid\DataModel;
use Ublaboo\DataGrid\DataSource\IDataSource;
use Ublaboo\DataGrid\Exception\DataGridException;

trait TDataGridAggregationFunction
{
	/**
	 * @var IAggregationFunction[]
	 */
	private $aggregationFunctions = [];

	/**
	 * @var IAggregationFunction|NULL
	 */
	private $multipleAggregationFunction;


	/**
	 * @param  string  $key
	 * @param  IAggregationFunction  $aggregationFunction
	 * @return static
	 * @throws DataGridException
	 */
	public function addAggregationFunction($key, IAggregationFunction $aggregationFunction)
	{
		if ($this->hasColumnsSummary()) {
			throw new DataGridException('You can use either ColumnsSummary or AggregationFunctions');
		}

		if (!$this->dataModel instanceof DataModel) {
			throw new DataGridException('You have to set a data source first.');
		}

		if (isset($this->aggregationFunctions[$key])) {
			throw new DataGridException('There is already a AggregationFunction defined on column ' . $key);
		}

		if ($this->multipleAggregationFunction instanceof MultipleAggregationFunction) {
			throw new DataGridException('You can not use both AggregationFunctions and MultipleAggregationFunction');
		}

		$this->aggregationFunctions[$key] = $aggregationFunction;

		return $this;
	}


	/**
	 * @param  IMultipleAggregationFunction  $multipleAggregationFunction
	 * @throws DataGridException
	 * @return static
	 */
	public function setMultipleAggregationFunction(IMultipleAggregationFunction $multipleAggregationFunction)
	{
		if ($this->hasColumnsSummary()) {
			throw new DataGridException('You can use either ColumnsSummary or AggregationFunctions');
		}

		if (!empty($this->aggregationFunctions)) {
			throw new DataGridException('You can not use both AggregationFunctions and MultipleAggregationFunction');
		}

		$this->multipleAggregationFunction = $multipleAggregationFunction;

		return $this;
	}


	/**
	 * @param  IDataSource  $dataSource
	 * @throws DataGridException
	 * @return void
	 */
	public function beforeDataModelFilter(IDataSource $dataSource)
	{
		if (!$this->hasSomeAggregationFunction()) {
			return;
		}

		if (!$dataSource instanceof IAggregatable) {
			throw new DataGridException('Used DataSource has to implement IAggregatable for aggegations to work');
		}

		if ($this->multipleAggregationFunction) {
			if ($this->multipleAggregationFunction->getFilterDataType() === IAggregationFunction::DATA_TYPE_ALL) {
				$dataSource->processAggregation([$this->multipleAggregationFunction, 'processDataSource']);
			}

			return;
		}

		foreach ($this->aggregationFunctions as $aggregationFunction) {
			if ($aggregationFunction->getFilterDataType() === IAggregationFunction::DATA_TYPE_ALL) {
				$dataSource->processAggregation([$aggregationFunction, 'processDataSource']);
			}
		}
	}


	/**
	 * @param  IDataSource  $dataSource
	 * @throws DataGridException
	 * @return void
	 */
	public function afterDataModelFilter(IDataSource $dataSource)
	{
		if (!$this->hasSomeAggregationFunction()) {
			return;
		}

		if (!$dataSource instanceof IAggregatable) {
			throw new DataGridException('Used DataSource has to implement IAggregatable for aggegations to work');
		}

		if ($this->multipleAggregationFunction) {
			if ($this->multipleAggregationFunction->getFilterDataType() === IAggregationFunction::DATA_TYPE_FILTERED) {
				$dataSource->processAggregation([$this->multipleAggregationFunction, 'processDataSource']);
			}

			return;
		}

		foreach ($this->aggregationFunctions as $aggregationFunction) {
			if ($aggregationFunction->getFilterDataType() === IAggregationFunction::DATA_TYPE_FILTERED) {
				$dataSource->processAggregation([$aggregationFunction, 'processDataSource']);
			}
		}
	}


	/**
	 * @param  IDataSource  $dataSource
	 * @throws DataGridException
	 * @return void
	 */
	public function afterDataModelPaginated(IDataSource $dataSource)
	{
		if (!$this->hasSomeAggregationFunction()) {
			return;
		}

		if (!$dataSource instanceof IAggregatable) {
			throw new DataGridException('Used DataSource has to implement IAggregatable for aggegations to work');
		}

		if ($this->multipleAggregationFunction) {
			if ($this->multipleAggregationFunction->getFilterDataType() === IAggregationFunction::DATA_TYPE_PAGINATED) {
				$dataSource->processAggregation([$this->multipleAggregationFunction, 'processDataSource']);
			}

			return;
		}

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
		return !empty($this->aggregationFunctions) || $this->multipleAggregationFunction;
	}


	/**
	 * @return IAggregationFunction[]
	 */
	public function getAggregationFunctions()
	{
		return $this->aggregationFunctions;
	}


	/**
	 * @return MultipleAggregationFunction
	 */
	public function getMultipleAggregationFunction()
	{
		return $this->multipleAggregationFunction;
	}
}
