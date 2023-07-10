<?php declare(strict_types = 1);

namespace Contributte\Datagrid\AggregationFunction;

use Contributte\Datagrid\DataModel;
use Contributte\Datagrid\DataSource\IDataSource;
use Contributte\Datagrid\Exception\DatagridException;

trait TDatagridAggregationFunction
{

	/** @var array|ISingleColumnAggregationFunction[] */
	private array $aggregationFunctions = [];

	private ?IMultipleAggregationFunction $multipleAggregationFunction = null;

	/**
	 * @return static
	 * @throws DatagridException
	 */
	public function addAggregationFunction(
		string $key,
		ISingleColumnAggregationFunction $aggregationFunction
	): self
	{
		if ($this->hasColumnsSummary()) {
			throw new DatagridException('You can use either ColumnsSummary or AggregationFunctions');
		}

		if (!$this->dataModel instanceof DataModel) {
			throw new DatagridException('You have to set a data source first.');
		}

		if (isset($this->aggregationFunctions[$key])) {
			throw new DatagridException('There is already a AggregationFunction defined on column ' . $key);
		}

		if ($this->multipleAggregationFunction instanceof IMultipleAggregationFunction) {
			throw new DatagridException('You can not use both AggregationFunctions and MultipleAggregationFunction');
		}

		$this->aggregationFunctions[$key] = $aggregationFunction;

		return $this;
	}

	/**
	 * @return static
	 * @throws DatagridException
	 */
	public function setMultipleAggregationFunction(
		IMultipleAggregationFunction $multipleAggregationFunction
	): self
	{
		if ($this->hasColumnsSummary()) {
			throw new DatagridException('You can use either ColumnsSummary or AggregationFunctions');
		}

		if ($this->aggregationFunctions !== []) {
			throw new DatagridException('You can not use both AggregationFunctions and MultipleAggregationFunction');
		}

		$this->multipleAggregationFunction = $multipleAggregationFunction;

		return $this;
	}

	/**
	 * @throws DatagridException
	 */
	public function beforeDataModelFilter(IDataSource $dataSource): void
	{
		if (!$this->hasSomeAggregationFunction()) {
			return;
		}

		if (!$dataSource instanceof IAggregatable) {
			throw new DatagridException('Used DataSource has to implement IAggregatable for aggegations to work');
		}

		if ($this->multipleAggregationFunction !== null) {
			$type = $this->multipleAggregationFunction->getFilterDataType();

			if ($type === IAggregationFunction::DATA_TYPE_ALL) {
				$dataSource->processAggregation($this->multipleAggregationFunction);
			}

			return;
		}

		foreach ($this->aggregationFunctions as $aggregationFunction) {
			if ($aggregationFunction->getFilterDataType() === IAggregationFunction::DATA_TYPE_ALL) {
				$dataSource->processAggregation($aggregationFunction);
			}
		}
	}

	/**
	 * @throws DatagridException
	 */
	public function afterDataModelFilter(IDataSource $dataSource): void
	{
		if (!$this->hasSomeAggregationFunction()) {
			return;
		}

		if (!$dataSource instanceof IAggregatable) {
			throw new DatagridException('Used DataSource has to implement IAggregatable for aggegations to work');
		}

		if ($this->multipleAggregationFunction !== null) {
			if ($this->multipleAggregationFunction->getFilterDataType() === IAggregationFunction::DATA_TYPE_FILTERED) {
				$dataSource->processAggregation($this->multipleAggregationFunction);
			}

			return;
		}

		foreach ($this->aggregationFunctions as $aggregationFunction) {
			if ($aggregationFunction->getFilterDataType() === IAggregationFunction::DATA_TYPE_FILTERED) {
				$dataSource->processAggregation($aggregationFunction);
			}
		}
	}

	/**
	 * @throws DatagridException
	 */
	public function afterDataModelPaginated(IDataSource $dataSource): void
	{
		if (!$this->hasSomeAggregationFunction()) {
			return;
		}

		if (!$dataSource instanceof IAggregatable) {
			throw new DatagridException('Used DataSource has to implement IAggregatable for aggegations to work');
		}

		if ($this->multipleAggregationFunction !== null) {
			if ($this->multipleAggregationFunction->getFilterDataType() === IAggregationFunction::DATA_TYPE_PAGINATED) {
				$dataSource->processAggregation($this->multipleAggregationFunction);
			}

			return;
		}

		foreach ($this->aggregationFunctions as $aggregationFunction) {
			if ($aggregationFunction->getFilterDataType() === IAggregationFunction::DATA_TYPE_PAGINATED) {
				$dataSource->processAggregation($aggregationFunction);
			}
		}
	}

	public function hasSomeAggregationFunction(): bool
	{
		return $this->aggregationFunctions !== [] || $this->multipleAggregationFunction !== null;
	}

	/**
	 * @return array<ISingleColumnAggregationFunction>
	 */
	public function getAggregationFunctions(): array
	{
		return $this->aggregationFunctions;
	}

	public function getMultipleAggregationFunction(): ?IMultipleAggregationFunction
	{
		return $this->multipleAggregationFunction;
	}

}
