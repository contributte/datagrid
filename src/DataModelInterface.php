<?php declare(strict_types = 1);
namespace Ublaboo\DataGrid;


use Ublaboo\DataGrid\Components\DataGridPaginator\DataGridPaginator;
use Ublaboo\DataGrid\DataSource\IDataSource;
use Ublaboo\DataGrid\Utils\Sorting;

/**
 * @method onBeforeFilter(IDataSource $dataSource)
 * @method onAfterFilter(IDataSource $dataSource)
 * @method onAfterPaginated(IDataSource $dataSource)
 */
interface DataModelInterface
{

	public function getDataSource(): IDataSource;


	public function filterData(?DataGridPaginator $paginatorComponent, Sorting $sorting, array $filters): iterable;


	/**
	 * @return mixed
	 */
	public function filterRow(array $condition);
}
