<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid;

use Ublaboo\DataGrid\DataSource\IDataSource;

class DataModel
{

	/**
	 * @var IDataSource
	 */
	protected $data_source;


	/**
	 * @param IDataSource $data_source
	 */
	public function __construct(IDataSource $data_source)
	{
		$this->data_source = $data_source;
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
	 * @param  sting                                          $sort
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
