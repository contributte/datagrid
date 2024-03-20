<?php declare(strict_types = 1);

namespace Contributte\Datagrid\AggregationFunction;

interface IAggregatable
{

	public function processAggregation(IAggregationFunction $function): void;

}
