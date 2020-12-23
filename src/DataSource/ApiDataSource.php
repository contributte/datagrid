<?php

declare(strict_types=1);

namespace Ublaboo\DataGrid\DataSource;

use Ublaboo\DataGrid\Utils\Sorting;
use UnexpectedValueException;

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


	// *******************************************************************************
	// *                          IDataSource implementation                         *
	// *******************************************************************************


	public function getCount(): int
	{
		return $this->getResponse(['count' => '']);
	}


	/**
	 * {@inheritDoc}
	 */
	public function getData(): array
	{
		return $this->data !== [] ? $this->data : $this->getResponse([
			'sort' => $this->sortColumn,
			'order' => $this->orderColumn,
			'limit' => $this->limit,
			'offset' => $this->offset,
			'filter' => $this->filter,
			'one' => $this->filterOne,
		]);
	}


	/**
	 * {@inheritDoc}
	 */
	public function filter(array $filters): void
	{
		/**
		 * First, save all filter values to array
		 */
		foreach ($filters as $filter) {
			if ($filter->isValueSet() && $filter->getConditionCallback() === null) {
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
			if ($filter->isValueSet() && $filter->getConditionCallback() !== null) {
				$this->data = (array) call_user_func_array(
					$filter->getConditionCallback(),
					[$this->data, $filter->getValue()]
				);
			}
		}
	}


	/**
	 * {@inheritDoc}
	 */
	public function filterOne(array $condition): IDataSource
	{
		$this->filter = $condition;
		$this->filterOne = 1;

		return $this;
	}


	public function limit(int $offset, int $limit): IDataSource
	{
		$this->offset = $offset;
		$this->limit = $limit;

		return $this;
	}


	public function sort(Sorting $sorting): IDataSource
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


	/**
	 * Get data of remote source
	 *
	 * @return mixed
	 */
	protected function getResponse(array $params = [])
	{
		$queryString = http_build_query($params + $this->queryParams);
		$url = sprintf('%s?%s', $this->url, $queryString);

		$content = file_get_contents($url);

		if ($content === false) {
			throw new UnexpectedValueException(sprintf('Could not open URL %s', $url));
		}

		return json_decode($content);
	}
}
