<?php

declare(strict_types = 1);

namespace Ublaboo\DataGrid;

use Nette\SmartObject;
use Ublaboo\DataGrid\Components\DataGridPaginator\DataGridPaginator;
use Ublaboo\DataGrid\DataSource\IDataSource;
use Ublaboo\DataGrid\Utils\Sorting;

/**
 * @method onBeforeFilter(IDataSource $dataSource)
 * @method onAfterFilter(IDataSource $dataSource)
 * @method onAfterPaginated(IDataSource $dataSource)
 */
final class RestBasedDataModel implements DataModelInterface
{

	use SmartObject;

	/**
	 * @var array|callable[]
	 */
	public $onBeforeFilter = [];

	/**
	 * @var array|callable[]
	 */
	public $onAfterFilter = [];

	/**
	 * @var array|callable[]
	 */
	public $onAfterPaginated = [];

	/**
	 * @var IDataSource
	 */
	private $dataSource;


	public function __construct(IDataSource $source)
	{
		$this->dataSource = $source;
	}


	public function getDataSource(): IDataSource
	{
		return $this->dataSource;
	}


	public function filterData(
		?DataGridPaginator $paginatorComponent,
		Sorting $sorting,
		array $filters
	): iterable
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


	/**
	 * @return mixed
	 */
	public function filterRow(array $condition)
	{
		$this->onBeforeFilter($this->dataSource);
		$this->onAfterFilter($this->dataSource);

		return $this->dataSource->filterOne($condition)->getData();
	}
}
