<?php declare(strict_types = 1);

namespace Contributte\Datagrid\DataSource;

use Contributte\Datagrid\Filter\FilterDate;
use Contributte\Datagrid\Filter\FilterDateRange;
use Contributte\Datagrid\Filter\FilterMultiSelect;
use Contributte\Datagrid\Filter\FilterRange;
use Contributte\Datagrid\Filter\FilterSelect;
use Contributte\Datagrid\Filter\FilterText;
use Contributte\Datagrid\Utils\DateTimeHelper;
use Contributte\Datagrid\Utils\Sorting;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\Response\Elasticsearch;
use RuntimeException;
use UnexpectedValueException;

class ElasticsearchDataSource extends FilterableDataSource implements IDataSource
{

	protected SearchParamsBuilder $searchParamsBuilder;

	/** @var callable */
	private $rowFactory;

	public function __construct(private Client $client, string $indexName, ?callable $rowFactory = null)
	{
		$this->searchParamsBuilder = new SearchParamsBuilder($indexName);

		if ($rowFactory === null) {
			$rowFactory = static fn (array $hit): array => $hit['_source'];
		}

		$this->rowFactory = $rowFactory;
	}

	public function getCount(): int
	{
		/** @var Elasticsearch $searchResult */
		$searchResult = $this->client->search($this->searchParamsBuilder->buildParams());

		if (!isset($searchResult['hits'])) {
			throw new UnexpectedValueException();
		}

		return is_array($searchResult['hits']['total'])
			? $searchResult['hits']['total']['value']
			: $searchResult['hits']['total'];
	}

	/**
	 * {@inheritDoc}
	 */
	public function getData(): array
	{
		/** @var Elasticsearch $searchResult */
		$searchResult = $this->client->search($this->searchParamsBuilder->buildParams());

		if (!isset($searchResult['hits'])) {
			throw new UnexpectedValueException();
		}

		return array_map($this->rowFactory, $searchResult['hits']['hits']);
	}

	/**
	 * {@inheritDoc}
	 */
	public function filterOne(array $condition): IDataSource
	{
		foreach ($condition as $value) {
			$this->searchParamsBuilder->addIdsQuery($value);
		}

		return $this;
	}

	public function limit(int $offset, int $limit): IDataSource
	{
		$this->searchParamsBuilder->setFrom($offset);
		$this->searchParamsBuilder->setSize($limit);

		return $this;
	}

	public function applyFilterDate(FilterDate $filter): void
	{
		foreach ($filter->getCondition() as $column => $value) {
			$timestampFrom = null;
			$timestampTo = null;

			if ($value) {
				$dateFrom = DateTimeHelper::tryConvertToDateTime($value, [$filter->getPhpFormat()]);
				$dateFrom->setTime(0, 0, 0);

				$timestampFrom = $dateFrom->getTimestamp();

				$dateTo = DateTimeHelper::tryConvertToDateTime($value, [$filter->getPhpFormat()]);
				$dateTo->setTime(23, 59, 59);

				$timestampTo = $dateTo->getTimestamp();

				$this->searchParamsBuilder->addRangeQuery($column, $timestampFrom, $timestampTo);
			}
		}
	}

	public function applyFilterDateRange(FilterDateRange $filter): void
	{
		foreach ($filter->getCondition() as $column => $values) {
			$timestampFrom = null;
			$timestampTo = null;

			if ($values['from']) {
				$dateFrom = DateTimeHelper::tryConvertToDateTime($values['from'], [$filter->getPhpFormat()]);
				$dateFrom->setTime(0, 0, 0);

				$timestampFrom = $dateFrom->getTimestamp();
			}

			if ($values['to']) {
				$dateTo = DateTimeHelper::tryConvertToDateTime($values['to'], [$filter->getPhpFormat()]);
				$dateTo->setTime(23, 59, 59);

				$timestampTo = $dateTo->getTimestamp();
			}

			if (is_int($timestampFrom) || is_int($timestampTo)) {
				$this->searchParamsBuilder->addRangeQuery($column, $timestampFrom, $timestampTo);
			}
		}
	}

	public function applyFilterRange(FilterRange $filter): void
	{
		foreach ($filter->getCondition() as $column => $value) {
			$this->searchParamsBuilder->addRangeQuery($column, $value['from'] ?? null, $value['to'] ?? null);
		}
	}

	public function applyFilterText(FilterText $filter): void
	{
		foreach ($filter->getCondition() as $column => $value) {
			if ($filter->isExactSearch()) {
				$this->searchParamsBuilder->addMatchQuery($column, $value);
			} else {
				$this->searchParamsBuilder->addPhrasePrefixQuery($column, $value);
			}
		}
	}

	public function applyFilterMultiSelect(FilterMultiSelect $filter): void
	{
		foreach ($filter->getCondition() as $column => $values) {
			$this->searchParamsBuilder->addBooleanMatchQuery($column, $values);
		}
	}

	public function applyFilterSelect(FilterSelect $filter): void
	{
		foreach ($filter->getCondition() as $column => $value) {
			$this->searchParamsBuilder->addMatchQuery($column, $value);
		}
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws RuntimeException
	 */
	public function sort(Sorting $sorting): IDataSource
	{
		if (is_callable($sorting->getSortCallback())) {
			throw new RuntimeException('No can do - not implemented yet');
		}

		foreach ($sorting->getSort() as $column => $order) {
			$this->searchParamsBuilder->setSort(
				[$column => ['order' => strtolower($order)]]
			);
		}

		return $this;
	}

	protected function getDataSource(): SearchParamsBuilder
	{
		return $this->searchParamsBuilder;
	}

}
