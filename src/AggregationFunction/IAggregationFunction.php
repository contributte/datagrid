<?php declare(strict_types = 1);

namespace Contributte\Datagrid\AggregationFunction;

use Dibi\Fluent;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\QueryBuilder;
use Nette\Database\Table\Selection;
use Nextras\Orm\Collection\ICollection;

interface IAggregationFunction
{

	public const DATA_TYPE_ALL = 'data_type_all';
	public const DATA_TYPE_FILTERED = 'data_type_filtered';
	public const DATA_TYPE_PAGINATED = 'data_type_paginated';

	public function getFilterDataType(): string;

	public function processDataSource(Fluent|QueryBuilder|Collection|Selection|ICollection $dataSource): void;

}
