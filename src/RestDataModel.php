<?php declare(strict_types = 1);

namespace Contributte\Datagrid;

use Contributte\Datagrid\Components\DatagridPaginator\DatagridPaginator;
use Contributte\Datagrid\DataSource\IDataSource;
use Contributte\Datagrid\Utils\Sorting;

class RestDataModel extends AbstractDataModel
{

	private IDataSource $dataSource;

	public function __construct(IDataSource $source)
	{
		$this->dataSource = $source;
	}

	public function getDataSource(): IDataSource
	{
		return $this->dataSource;
	}

	public function filterData(?DatagridPaginator $paginatorComponent, Sorting $sorting, array $filters): iterable
	{
		$this->onBeforeFilter($this->dataSource);

		$this->dataSource->filter($filters);

		$this->onAfterFilter($this->dataSource);

		/**
		 * Paginator is optional
		 */
		if ($paginatorComponent !== null) {
			$paginator = $paginatorComponent->getPaginator();

			$this->dataSource->sort($sorting)->limit(
				$paginator->getOffset(),
				$paginator->getItemsPerPage()
			);

			$this->onAfterPaginated($this->dataSource);

			$data = $this->dataSource->getData();
			$paginator->setItemCount($this->dataSource->getCount());

			return $data;
		}

		return $this->dataSource->sort($sorting)->getData();
	}

	public function filterRow(array $condition): array
	{
		$this->onBeforeFilter($this->dataSource);
		$this->onAfterFilter($this->dataSource);

		return $this->dataSource->filterOne($condition)->getData();
	}

}
