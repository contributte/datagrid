<?php

declare(strict_types=1);

namespace Ublaboo\DataGrid\AggregationFunction;

interface IAggregatable
{

	public function processAggregation(IAggregationFunction $function): void;
}
