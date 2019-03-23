<?php declare(strict_types=1);

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
	protected $queryParams;

	/**
	 * @var string|null
	 */
	protected $sortColumn;

	/**
	 * @var string|null
	 */
	protected $orderColumn;

	/**
	 * @var int|null
	 */
	protected $limit;

	/**
	 * @var int|null
	 */
	protected $offset;

	/**
	 * @var int
	 */
	protected $filterOne = 0;

	/**
	 * @var array
	 */
	protected $filter = [];


	public function __construct(string $url, array $queryParams = [])
	{
		$this->url = $url;
		$this->queryParams = $queryParams;
	}


	/**
	 * Get data of remote source
	 * @return mixed
	 */
	protected function getResponse(array $params = [])
	{
		$queryString = http_build_query($params + $this->queryParams);

		return json_decode(file_get_contents(sprintf('%s?%s', $this->url, $queryString)));
	}


	/********************************************************************************
	 *                          IDataSource implementation                          *
	 ********************************************************************************/


	public function getCount(): int
	{
		return $this->getResponse(['count' => '']);
	}


	public function getData(): array
	{
		return !empty($this->data) ? $this->data : $this->getResponse([
			'sort' => $this->sortColumn,
			'order' => $this->orderColumn,
			'limit' => $this->limit,
			'offset' => $this->offset,
			'filter' => $this->filter,
			'one' => $this->filterOne,
		]);
	}


	public function filter(array $filters): self
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


	public function filterOne(array $condition): self
	{
		$this->filter = $condition;
		$this->filterOne = 1;

		return $this;
	}


	public function limit(int $offset, int $limit): self
	{
		$this->offset = $offset;
		$this->limit = $limit;

		return $this;
	}


	public function sort(Sorting $sorting): self
	{
		/**
		 * there is only one iteration
		 */
		foreach ($sorting->getSort() as $column => $order) {
			$this->sortColumn = $column;
			$this->orderColumn = $order;
		}

		return $this;
	}
}
