<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\DataSource;

use Ublaboo\DataGrid\Utils\Sorting;

class ApiDataSource implements IDataSource
{

	/**
	 * @var array
	 */
	protected $data = [];

	/**
	 * @var string
	 */
	protected $url;

	/**
	 * @var array
	 */
	protected $query_params;

	/**
	 * @var string
	 */
	protected $sort_column;

	/**
	 * @var string
	 */
	protected $order_column;

	/**
	 * @var int
	 */
	protected $limit;

	/**
	 * @var int
	 */
	protected $offset;

	/**
	 * @var int
	 */
	protected $filter_one = 0;

	/**
	 * @var array
	 */
	protected $filter = [];


	/**
	 * @param string $url
	 */
	public function __construct($url, array $query_params = [])
	{
		$this->url = $url;
		$this->query_params = $query_params;
	}


	/**
	 * Get data of remote source
	 * @param  array  $params
	 * @return mixed
	 */
	protected function getResponse(array $params = [])
	{
		$query_string = http_build_query($params + $this->query_params);

		return json_decode(file_get_contents("{$this->url}?$query_string"));
	}


	/********************************************************************************
	 *                          IDataSource implementation                          *
	 ********************************************************************************/


	/**
	 * Get count of data
	 * @return int
	 */
	public function getCount()
	{
		return $this->getResponse(['count' => '']);
	}


	/**
	 * Get the data
	 * @return array
	 */
	public function getData()
	{
		return !empty($this->data) ? $this->data : $this->getResponse([
			'sort' => $this->sort_column,
			'order' => $this->order_column,
			'limit' => $this->limit,
			'offset' => $this->offset,
			'filter' => $this->filter,
			'one' => $this->filter_one,
		]);
	}


	/**
	 * Filter data
	 * @param array $filters
	 * @return static
	 */
	public function filter(array $filters)
	{
		/**
		 * First, save all filter values to array
		 */
		foreach ($filters as $filter) {
			if ($filter->isValueSet() && !$filter->hasConditionCallback()) {
				$this->filter[$filter->getKey()] = $filter->getCondition();
			}
		}

		/**
		 * Download filtered data
		 */
		$this->data = $this->getData();

		/**
		 * Apply possible user filter callbacks
		 */
		foreach ($filters as $filter) {
			if ($filter->isValueSet() && $filter->hasConditionCallback()) {
				$this->data = (array) call_user_func_array(
					$filter->getConditionCallback(),
					[$this->data, $filter->getValue()]
				);
			}
		}

		return $this;
	}


	/**
	 * Filter data - get one row
	 * @param array $condition
	 * @return static
	 */
	public function filterOne(array $condition)
	{
		$this->filter = $condition;
		$this->filter_one = 1;

		return $this;
	}


	/**
	 * Apply limit and offset on data
	 * @param int $offset
	 * @param int $limit
	 * @return static
	 */
	public function limit($offset, $limit)
	{
		$this->offset = $offset;
		$this->limit = $limit;

		return $this;
	}


	/**
	 * Sort data
	 * @param Sorting $sorting
	 * @return static
	 */
	public function sort(Sorting $sorting)
	{
		/**
		 * there is only one iteration
		 */
		foreach ($sorting->getSort() as $column => $order) {
			$this->sort_column = $column;
			$this->order_column = $order;
		}

		return $this;
	}
}
