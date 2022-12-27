<?php declare(strict_types = 1);

namespace Ublaboo\DataGrid\AggregationFunction;

interface IMultipleAggregationFunction extends IAggregationFunction
{

	public function renderResult(string $key): mixed;

}
