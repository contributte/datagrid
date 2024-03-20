<?php declare(strict_types = 1);

namespace Contributte\Datagrid\DataSource;

final class SearchParamsBuilder
{

	private array $sort = [];

	private ?int $from = null;

	private ?int $size = null;

	private array $phrasePrefixQueries = [];

	private array $matchQueries = [];

	private array $booleanMatchQueries = [];

	private array $rangeQueries = [];

	private array $idsQueries = [];

	public function __construct(private string $indexName)
	{
	}

	public function addPhrasePrefixQuery(string $field, string $query): void
	{
		$this->phrasePrefixQueries[] = [$field => $query];
	}

	public function addMatchQuery(string $field, mixed $query): void
	{
		$this->matchQueries[] = [$field => $query];
	}

	public function addBooleanMatchQuery(string $field, array $queries): void
	{
		$this->booleanMatchQueries[] = [$field => $queries];
	}

	public function addRangeQuery(string $field, ?int $from, ?int $to): void
	{
		$this->rangeQueries[] = [$field => ['from' => $from, 'to' => $to]];
	}

	public function addIdsQuery(array $ids): void
	{
		$this->idsQueries[] = $ids;
	}

	public function setSort(array $sort): void
	{
		$this->sort = $sort;
	}

	public function setFrom(int $from): void
	{
		$this->from = $from;
	}

	public function setSize(int $size): void
	{
		$this->size = $size;
	}

	public function buildParams(): array
	{
		$return = [
			'index' => $this->indexName,
		];

		if ($this->sort !== [] || ($this->from !== null) || ($this->size !== null)) {
			$return['body'] = [];
		}

		if ($this->sort !== []) {
			$return['body']['sort'] = $this->sort;
		}

		if ($this->from !== null) {
			$return['body']['from'] = $this->from;
		}

		if ($this->size !== null) {
			$return['body']['size'] = $this->size;
		}

		if ($this->phrasePrefixQueries === []
			&& $this->matchQueries === []
			&& $this->booleanMatchQueries === []
			&& $this->rangeQueries === []
			&& $this->idsQueries === []) {
			return $return;
		}

		$return['body']['query'] = [
			'bool' => [
				'must' => [],
			],
		];

		foreach ($this->phrasePrefixQueries as $phrasePrefixQuery) {
			foreach ($phrasePrefixQuery as $field => $query) {
				$return['body']['query']['bool']['must'][] = [
					'multi_match' => [
						'query' => $query,
						'type' => 'phrase_prefix',
						'fields' => [$field],
					],
				];
			}
		}

		foreach ($this->matchQueries as $matchQuery) {
			foreach ($matchQuery as $field => $query) {
				$return['body']['query']['bool']['must'][] = [
					'match' => [
						$field => [
							'query' => $query,
						],
					],
				];
			}
		}

		foreach ($this->booleanMatchQueries as $booleanMatchQuery) {
			foreach ($booleanMatchQuery as $field => $queries) {
				if ($queries === []) {
					continue;
				}

				$boolFilter = [];

				foreach ($queries as $query) {
					$boolFilter[] = [
						'match' => [
							$field => [
								'query' => $query,
							],
						],
					];
				}

				$return['body']['query']['bool']['must'][] = [
					'bool' => [
						'should' => [$boolFilter],
					],
				];
			}
		}

		foreach ($this->rangeQueries as $rangeQuery) {
			foreach ($rangeQuery as $field => $range) {
				if ($range['from'] === null && $range['to'] === null) {
					continue;
				}

				$rangeFilter = ['range' => [$field => []]];

				if ($range['from'] !== null) {
					$rangeFilter['range'][$field]['gte'] = $range['from'];
				}

				if ($range['to'] !== null) {
					$rangeFilter['range'][$field]['lte'] = $range['to'];
				}

				$return['body']['query']['bool']['must'][] = $rangeFilter;
			}
		}

		foreach ($this->idsQueries as $ids) {
			$return['body']['query']['bool']['must'][] = [
				'ids' => [
					'values' => $ids,
				],
			];
		}

		return $return;
	}

}
