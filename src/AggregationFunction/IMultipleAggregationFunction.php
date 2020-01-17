<?php

declare(strict_types=1);

namespace Ublaboo\DataGrid\AggregationFunction;

interface IMultipleAggregationFunction extends IAggregationFunction
{

	/**
	 * @return mixed
	 */
	public function renderResult(?string $key = null);
}
