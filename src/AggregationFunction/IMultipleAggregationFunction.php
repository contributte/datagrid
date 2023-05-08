<?php declare(strict_types = 1);

namespace Contributte\Datagrid\AggregationFunction;

interface IMultipleAggregationFunction extends IAggregationFunction
{

	public function renderResult(string $key): mixed;

}
