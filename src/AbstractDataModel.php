<?php declare(strict_types = 1);

namespace Contributte\Datagrid;

use Contributte\Datagrid\Components\DatagridPaginator\DatagridPaginator;
use Contributte\Datagrid\DataSource\IDataSource;
use Contributte\Datagrid\Utils\Sorting;

/**
 * @method onBeforeFilter(IDataSource $dataSource)
 * @method onAfterFilter(IDataSource $dataSource)
 * @method onAfterPaginated(IDataSource $dataSource)
 */
abstract class AbstractDataModel
{

	/** @var array|callable[] */
	public array $onBeforeFilter = [];

	/** @var array|callable[] */
	public array $onAfterFilter = [];

	/** @var array|callable[] */
	public array $onAfterPaginated = [];

	abstract public function getDataSource(): IDataSource;

	abstract public function filterData(?DatagridPaginator $paginatorComponent, Sorting $sorting, array $filters): iterable;

	abstract public function filterRow(array $condition): array;

}
