<?php declare(strict_types = 1);

namespace Contributte\Datagrid\DataSource;

use Contributte\Datagrid\Utils\Sorting;
use UnexpectedValueException;

class ApiDataSource implements IDataSource
{

	protected array $data = [];

	protected ?string $sortColumn = null;

	protected ?string $orderColumn = null;

	protected ?int $limit = null;

	protected ?int $offset = null;

	protected int $filterOne = 0;

	protected array $filter = [];

	public function __construct(protected string $url, protected array $queryParams = [])
	{
	}

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
	 */
	protected function getResponse(array $params = []): mixed
	{
		$queryString = http_build_query($params + $this->queryParams);
		$url = sprintf('%s?%s', $this->url, $queryString);

		$content = file_get_contents($url);

		if ($content === false) {
			throw new UnexpectedValueException(sprintf('Could not open URL %s', $url));
		}

		return json_decode($content, null, 512, JSON_THROW_ON_ERROR);
	}

}
