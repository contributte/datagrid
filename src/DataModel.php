<?php declare(strict_types = 1);

namespace Ublaboo\DataGrid;

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
			throw new DataGridWrongDataSourceException(sprintf(
				'DataGrid can not take [%s] as data source.',
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

	public function filterRow(array $condition): mixed
	{
		$this->onBeforeFilter($this->dataSource);
		$this->onAfterFilter($this->dataSource);

		return $this->dataSource->filterOne($condition)->getData();
	}

}
