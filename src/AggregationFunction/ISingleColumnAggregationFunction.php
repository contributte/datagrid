<?php declare(strict_types = 1);

namespace Contributte\Datagrid\AggregationFunction;

interface ISingleColumnAggregationFunction extends IAggregationFunction
{

	public function renderResult(): mixed;

}
