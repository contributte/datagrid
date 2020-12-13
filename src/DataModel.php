<?php

declare(strict_types=1);

namespace Ublaboo\DataGrid;

use Dibi;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\QueryBuilder;
use Nette\Database\Drivers as NDBDrivers;
use Nette\Database\Table\Selection;
use Nette\SmartObject;
use Nextras\Orm\Collection\ICollection;
use Ublaboo\DataGrid\Components\DataGridPaginator\DataGridPaginator;
use Ublaboo\DataGrid\DataSource\ArrayDataSource;
use Ublaboo\DataGrid\DataSource\DibiFluentDataSource;
use Ublaboo\DataGrid\DataSource\DibiFluentMssqlDataSource;
use Ublaboo\DataGrid\DataSource\DibiFluentPostgreDataSource;
use Ublaboo\DataGrid\DataSource\DoctrineCollectionDataSource;
use Ublaboo\DataGrid\DataSource\DoctrineDataSource;
use Ublaboo\DataGrid\DataSource\IDataSource;
use Ublaboo\DataGrid\DataSource\NetteDatabaseTableDataSource;
use Ublaboo\DataGrid\DataSource\NetteDatabaseTableMssqlDataSource;
use Ublaboo\DataGrid\DataSource\NextrasDataSource;
use Ublaboo\DataGrid\Exception\DataGridWrongDataSourceException;
use Ublaboo\DataGrid\Utils\NetteDatabaseSelectionHelper;
use Ublaboo\DataGrid\Utils\Sorting;

/**
 * @method onBeforeFilter(IDataSource $dataSource)
 * @method onAfterFilter(IDataSource $dataSource)
 * @method onAfterPaginated(IDataSource $dataSource)
 */
final class DataModel
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

	/**
	 * @param mixed $source
	 */
	public function __construct($source, string $primaryKey)
	{
		if ($source instanceof IDataSource) {
			/**
			 * Custom user datasource is ready for use
			 *
			 * $source = $source;
			 */
		} elseif (is_array($source)) {
			$source = new ArrayDataSource($source);

		} elseif ($source instanceof Dibi\Fluent) {
			$driver = $source->getConnection()->getDriver();

			if ($driver instanceof Dibi\Drivers\OdbcDriver) {
				$source = new DibiFluentMssqlDataSource($source, $primaryKey);

			} elseif ($driver instanceof Dibi\Drivers\MsSqlDriver) {
				$source = new DibiFluentMssqlDataSource($source, $primaryKey);

			} elseif ($driver instanceof Dibi\Drivers\PostgreDriver) {
				$source = new DibiFluentPostgreDataSource($source, $primaryKey);

			} elseif ($driver instanceof Dibi\Drivers\SqlsrvDriver) {
				$source = new DibiFluentMssqlDataSource($source, $primaryKey);

			} else {
				$source = new DibiFluentDataSource($source, $primaryKey);
			}
		} elseif ($source instanceof Selection) {
			$driver = NetteDatabaseSelectionHelper::getDriver($source);

			if ($driver instanceof NDBDrivers\MsSqlDriver || $driver instanceof NDBDrivers\SqlsrvDriver) {
				$source = new NetteDatabaseTableMssqlDataSource($source, $primaryKey);
			} else {
				$source = new NetteDatabaseTableDataSource($source, $primaryKey);
			}
		} elseif ($source instanceof QueryBuilder) {
			$source = new DoctrineDataSource($source, $primaryKey);

		} elseif ($source instanceof Collection) {
			$source = new DoctrineCollectionDataSource($source, $primaryKey);

		} elseif ($source instanceof ICollection) {
			$source = new NextrasDataSource($source, $primaryKey);

		} else {
			throw new DataGridWrongDataSourceException(sprintf(
				'DataGrid can not take [%s] as data source.',
				is_object($source) ? get_class($source) : 'null'
			));
		}

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
			$paginator->setItemCount($this->dataSource->getCount());

			$this->dataSource->sort($sorting)->limit(
				$paginator->getOffset(),
				$paginator->getItemsPerPage()
			);

			$this->onAfterPaginated($this->dataSource);

			return $this->dataSource->getData();
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
