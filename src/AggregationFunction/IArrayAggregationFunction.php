<?php declare(strict_types = 1);

namespace Contributte\Datagrid\AggregationFunction;

interface IArrayAggregationFunction extends IAggregationFunction
{

	/**
	 * @param array<mixed> $data Rows as held by ArrayDataSource
	 */
	public function processArray(array $data): void;

}
