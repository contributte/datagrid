<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid;

use Nette;
use Dibi;
use DibiFluent;
use Nette\Database\Table\Selection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\QueryBuilder;
use Ublaboo\DataGrid\DataSource\IDataSource;
use Ublaboo\DataGrid\Exception\DataGridWrongDataSourceException;
use Ublaboo\DataGrid\Utils\Sorting;
use Ublaboo\DataGrid\Utils\NetteDatabaseSelectionHelper;
use Nette\Database\Drivers as NDBDrivers;
use Ublaboo\DataGrid\DataSource\ApiDataSource;
use Nextras\Orm\Collection\ICollection;

final class DataModel extends Nette\Object
{

	/**
	 * @var callable[]
	 */
	public $onBeforeFilter = [];

	/**
	 * @var callable[]
	 */
	public $onAfterFilter = [];

	/**
	 * @var callable[]
	 */
	public $onAfterPaginated = [];

	/**
	 * @var IDataSource
	 */
	private $data_source;


	/**
	 * @param IDataSource|array|Dibi\Fluent|Selection|QueryBuilder|Collection $source
	 * @param string $primary_key
	 */
	public function __construct($source, $primary_key)
	{
		if ($source instanceof IDataSource || $source instanceof ApiDataSource) {
			/**
			 * Custom user datasource is ready for use
			 *
			 * $source = $source;
			 */

		} else if (is_array($source)) {
			$source = new DataSource\ArrayDataSource($source);

		} else if ($source instanceof Dibi\Fluent || $source instanceof DibiFluent) {
			$driver = $source->getConnection()->getDriver();

			if ($driver instanceof Dibi\Drivers\OdbcDriver) {
				$source = new DataSource\DibiFluentMssqlDataSource($source, $primary_key);

			} else if ($driver instanceof Dibi\Drivers\MsSqlDriver) {
				$source = new DataSource\DibiFluentMssqlDataSource($source, $primary_key);

			} else if ($driver instanceof Dibi\Drivers\SqlsrvDriver) {
				$source = new DataSource\DibiFluentMssqlDataSource($source, $primary_key);

			} else {
				$source = new DataSource\DibiFluentDataSource($source, $primary_key);
			}

		} else if ($source instanceof Selection) {
			$driver = NetteDatabaseSelectionHelper::getDriver($source);

			if ($driver instanceof NDBDrivers\MsSqlDriver || $driver instanceof NDBDrivers\SqlsrvDriver) {
				$source = new DataSource\NetteDatabaseTableMssqlDataSource($source, $primary_key);
			} else {
				$source = new DataSource\NetteDatabaseTableDataSource($source, $primary_key);
			}

		} else if ($source instanceof QueryBuilder) {
			$source = new DataSource\DoctrineDataSource($source, $primary_key);

		} else if ($source instanceof Collection) {
			$source = new DataSource\DoctrineCollectionDataSource($source, $primary_key);

		} elseif ($source instanceof ICollection) {
			$source = new DataSource\NextrasDataSource($source, $primary_key);

		} else {
			throw new DataGridWrongDataSourceException(sprintf(
				"DataGrid can not take [%s] as data source.",
				is_object($source) ? get_class($source) : 'NULL'
			));
		}

		$this->data_source = $source;
	}


	/**
	 * Return dat asource
	 * @return IDataSource
	 */
	public function getDataSource()
	{
		return $this->data_source;
	}


	/**
	 * Filter/paginate/limit/order data source and return reset of data in array
	 * @param  Components\DataGridPaginator\DataGridPaginator $paginator_component
	 * @param  Sorting                                        $sorting
	 * @param  array                                          $filters
	 * @return array
	 */
	public function filterData(
		Components\DataGridPaginator\DataGridPaginator $paginator_component = NULL,
		Sorting $sorting,
		array $filters
	) {
		$this->onBeforeFilter($this->data_source);

		$this->data_source->filter($filters);

		$this->onAfterFilter($this->data_source);

		/**
		 * Paginator is optional
		 */
		if ($paginator_component) {
			$paginator = $paginator_component->getPaginator();
			$paginator->setItemCount($this->data_source->getCount());

			$this->data_source->sort($sorting)->limit(
				$paginator->getOffset(),
				$paginator->getItemsPerPage()
			);

			$this->onAfterPaginated($this->data_source);

			return $this->data_source->getData();
		}

		return $this->data_source->sort($sorting)->getData();
	}


	/**
	 * Filter one row
	 * @param  array $condition
	 * @return mixed
	 */
	public function filterRow(array $condition)
	{
		$this->onBeforeFilter($this->data_source);
		$this->onAfterFilter($this->data_source);

		return $this->data_source->filterOne($condition)->getData();
	}

}
