<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid;

use Nette;
use Ublaboo\DataGrid\DataSource\IDataSource;
use Ublaboo\DataGrid\Exception\DataGridWrongDataSourceException;

class DataModel
{

	/**
	 * @var IDataSource
	 */
	protected $data_source;


	/**
	 * @param IDataSource|array|\DibiFluent|Nette\Database\Table\Selection|\Kdyby\Doctrine\QueryBuilder $source
	 * @param string $primary_key
	 */
	public function __construct($source, $primary_key)
	{
		if ($source instanceof IDataSource) {
			/**
			 * Custom user datasource is ready for use
			 */
			$source = $source;

		} else if (is_array($source)) {
			$source = new DataSource\ArrayDataSource($source);

		} else if ($source instanceof \DibiFluent) {
			$driver = $source->getConnection()->getDriver();

			if ($driver instanceof \DibiOdbcDriver) {
				$source = new DataSource\DibiFluentMssqlDataSource($source, $primary_key);

			} else if ($driver instanceof \DibiMsSqlDriver) {
				$source = new DataSource\DibiFluentMssqlDataSource($source, $primary_key);

			} else {
				$source = new DataSource\DibiFluentDataSource($source, $primary_key);
			}

		} else if ($source instanceof Nette\Database\Table\Selection) {
			$source = new DataSource\NetteDatabaseTableDataSource($source, $primary_key);

		} else if ($source instanceof \Kdyby\Doctrine\QueryBuilder) {
			$source = new DataSource\DoctrineDataSource($source, $primary_key);

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
	 * @param  string                                          $sort
	 * @param  array                                          $filters
	 * @return array
	 */
	public function filterData(
		Components\DataGridPaginator\DataGridPaginator $paginator_component = NULL,
		$sort,
		array $filters
	) {
		$this->data_source->filter($filters);

		/**
		 * Paginator is optional
		 */
		if ($paginator_component) {
			$paginator = $paginator_component->getPaginator();
			$paginator->setItemCount($this->data_source->getCount());

			$this->data_source->sort($sort)->limit(
				$paginator->getOffset(),
				$paginator->getItemsPerPage()
			);
		}

		return $this->data_source->getData();
	}


	/**
	 * Filter one row
	 * @param  array  $condition
	 * @return mixed
	 */
	public function filterRow(array $condition)
	{
		return $this->data_source->filterOne($condition)->getData();
	}

}
