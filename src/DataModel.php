<?php declare(strict_types = 1);

namespace Contributte\Datagrid;

use Contributte\Datagrid\Components\DatagridPaginator\DatagridPaginator;
use Contributte\Datagrid\DataSource\ArrayDataSource;
use Contributte\Datagrid\DataSource\DibiFluentDataSource;
use Contributte\Datagrid\DataSource\DibiFluentMssqlDataSource;
use Contributte\Datagrid\DataSource\DibiFluentPostgreDataSource;
use Contributte\Datagrid\DataSource\DoctrineCollectionDataSource;
use Contributte\Datagrid\DataSource\DoctrineDataSource;
use Contributte\Datagrid\DataSource\IDataSource;
use Contributte\Datagrid\DataSource\NetteDatabaseTableDataSource;
use Contributte\Datagrid\DataSource\NetteDatabaseTableMssqlDataSource;
use Contributte\Datagrid\DataSource\NextrasDataSource;
use Contributte\Datagrid\Exception\DatagridWrongDataSourceException;
use Contributte\Datagrid\Utils\NetteDatabaseSelectionHelper;
use Contributte\Datagrid\Utils\Sorting;
use Dibi\Drivers\MsSqlDriver;
use Dibi\Drivers\OdbcDriver;
use Dibi\Drivers\PostgreDriver;
use Dibi\Drivers\SqlsrvDriver;
use Dibi\Fluent;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\QueryBuilder;
use Nette\Database\Drivers\MsSqlDriver as NDBMsSqlDriver;
use Nette\Database\Drivers\SqlsrvDriver as NDBSqlsrvDriver;
use Nette\Database\Table\Selection;
use Nette\SmartObject;
use Nextras\Orm\Collection\ICollection;

/**
 * @method onBeforeFilter(IDataSource $dataSource)
 * @method onAfterFilter(IDataSource $dataSource)
 * @method onAfterPaginated(IDataSource $dataSource)
 */
final class DataModel
{

	use SmartObject;

	/** @var array|callable[] */
	public array $onBeforeFilter = [];

	/** @var array|callable[] */
	public array $onAfterFilter = [];

	/** @var array|callable[] */
	public array $onAfterPaginated = [];

	private IDataSource $dataSource;

	public function __construct(mixed $source, string $primaryKey)
	{
		if (is_array($source)) {
			$source = new ArrayDataSource($source);

		} elseif ($source instanceof Fluent) {
			$driver = $source->getConnection()->getDriver();

			if ($driver instanceof OdbcDriver) {
				$source = new DibiFluentMssqlDataSource($source, $primaryKey);

			} elseif ($driver instanceof MsSqlDriver) {
				$source = new DibiFluentMssqlDataSource($source, $primaryKey);

			} elseif ($driver instanceof PostgreDriver) {
				$source = new DibiFluentPostgreDataSource($source, $primaryKey);

			} elseif ($driver instanceof SqlsrvDriver) {
				$source = new DibiFluentMssqlDataSource($source, $primaryKey);

			} else {
				$source = new DibiFluentDataSource($source, $primaryKey);
			}
		} elseif ($source instanceof Selection) {
			$driver = NetteDatabaseSelectionHelper::getDriver($source);

			$source = $driver instanceof NDBMsSqlDriver || $driver instanceof NDBSqlsrvDriver ? new NetteDatabaseTableMssqlDataSource($source, $primaryKey) : new NetteDatabaseTableDataSource($source, $primaryKey);
		} elseif ($source instanceof QueryBuilder) {
			$source = new DoctrineDataSource($source, $primaryKey);

		} elseif ($source instanceof Collection) {
			$source = new DoctrineCollectionDataSource($source, $primaryKey);

		} elseif ($source instanceof ICollection) {
			$source = new NextrasDataSource($source, $primaryKey);

		} elseif (!($source instanceof IDataSource)) {
			throw new DatagridWrongDataSourceException(sprintf(
				'Datagrid can not take [%s] as data source.',
				is_object($source) ? $source::class : 'null'
			));
		}

		$this->dataSource = $source;
	}

	public function getDataSource(): IDataSource
	{
		return $this->dataSource;
	}

	public function filterData(
		?DatagridPaginator $paginatorComponent,
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

	public function filterRow(array $condition): mixed
	{
		$this->onBeforeFilter($this->dataSource);
		$this->onAfterFilter($this->dataSource);

		return $this->dataSource->filterOne($condition)->getData();
	}

}
