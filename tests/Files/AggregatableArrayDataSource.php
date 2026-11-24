<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Tests\Files;

use Contributte\Datagrid\AggregationFunction\IAggregatable;
use Contributte\Datagrid\AggregationFunction\IAggregationFunction;
use Contributte\Datagrid\DataSource\ArrayDataSource;
use Doctrine\Common\Collections\ArrayCollection;

final class AggregatableArrayDataSource extends ArrayDataSource implements IAggregatable
{

	public function processAggregation(IAggregationFunction $function): void
	{
		$function->processDataSource(new ArrayCollection(array_map(fn (array $row): object => (object) $row, $this->data)));
	}

}
