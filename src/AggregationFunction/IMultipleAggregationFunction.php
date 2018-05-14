<?php declare(strict_types = 1);

namespace Ublaboo\DataGrid\AggregationFunction;

interface IMultipleAggregationFunction
{

	public const DATA_TYPE_ALL = IAggregationFunction::DATA_TYPE_ALL;
	public const DATA_TYPE_FILTERED = IAggregationFunction::DATA_TYPE_FILTERED;
	public const DATA_TYPE_PAGINATED = IAggregationFunction::DATA_TYPE_PAGINATED;

	public function getFilterDataType(): string;

	/**
	 * @param  mixed $data_source
	 */
	public function processDataSource($data_source): void;

	/**
	 * @return mixed
	 */
	public function renderResult(string $key);

}
