<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Tests\Cases\DataSources;

use Contributte\Datagrid\DataSource\SearchParamsBuilder;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';

final class SearchParamsBuilderTest extends TestCase
{

	private SearchParamsBuilder $searchParamsBuilder;

	public function setUp(): void
	{
		$this->searchParamsBuilder = new SearchParamsBuilder('users', 'user');
	}

	public function testEmptyQuery(): void
	{
		Assert::same(
			[
				'index' => 'users',
			],
			$this->searchParamsBuilder->buildParams()
		);
	}

	public function testSort(): void
	{
		$this->searchParamsBuilder->setSort(['name' => ['order' => 'desc']]);

		Assert::same(
			[
				'index' => 'users',
				'body' => [
					'sort' => ['name' => ['order' => 'desc']],
				],
			],
			$this->searchParamsBuilder->buildParams()
		);
	}

	public function testPagination(): void
	{
		$this->searchParamsBuilder->setFrom(0);
		$this->searchParamsBuilder->setSize(20);

		Assert::same(
			[
				'index' => 'users',
				'body' => [
					'from' => 0,
					'size' => 20,
				],
			],
			$this->searchParamsBuilder->buildParams()
		);
	}

	public function testPhrasePrefixQuery(): void
	{
		$this->searchParamsBuilder->addPhrasePrefixQuery('name', 'john');

		Assert::same(
			[
				'index' => 'users',
				'body' => [
					'query' => [
						'bool' => [
							'must' => [
								[
									'multi_match' => [
										'query' => 'john',
										'type' => 'phrase_prefix',
										'fields' => ['name'],
									],
								],
							],
						],
					],
				],
			],
			$this->searchParamsBuilder->buildParams()
		);
	}

	public function testMatchQuery(): void
	{
		$this->searchParamsBuilder->addMatchQuery('name', 'john');

		Assert::same(
			[
				'index' => 'users',
				'body' => [
					'query' => [
						'bool' => [
							'must' => [
								[
									'match' => [
										'name' => [
											'query' => 'john',
										],
									],
								],
							],
						],
					],
				],
			],
			$this->searchParamsBuilder->buildParams()
		);
	}

	public function testBooleanMatchQuery(): void
	{
		$this->searchParamsBuilder->addBooleanMatchQuery('status', ['active', 'disabled']);

		Assert::same(
			[
				'index' => 'users',
				'body' => [
					'query' => [
						'bool' => [
							'must' => [
								[
									'bool' => [
										'should' => [
											[
												[
													'match' => [
														'status' => [
															'query' => 'active',
														],
													],
												],
												[
													'match' => [
														'status' => [
															'query' => 'disabled',
														],
													],
												],
											],
										],
									],
								],
							],
						],
					],
				],
			],
			$this->searchParamsBuilder->buildParams()
		);
	}

	public function testRangeQuery(): void
	{
		$this->searchParamsBuilder->addRangeQuery('score', 8, 64);

		Assert::same(
			[
				'index' => 'users',
				'body' => [
					'query' => [
						'bool' => [
							'must' => [
								[
									'range' => [
										'score' => [
											'gte' => 8,
											'lte' => 64,
										],
									],
								],
							],
						],
					],
				],
			],
			$this->searchParamsBuilder->buildParams()
		);
	}

	public function testIdsQuery(): void
	{
		$this->searchParamsBuilder->addIdsQuery([0, 1, 1, 2, 3, 5, 8]);

		Assert::same(
			[
				'index' => 'users',
				'body' => [
					'query' => [
						'bool' => [
							'must' => [
								[
									'ids' => [
										'values' => [
											0,
			1,
			1,
			2,
			3,
			5,
			8,
										],
									],
								],
							],
						],
					],
				],
			],
			$this->searchParamsBuilder->buildParams()
		);
	}

	public function testAllTogether(): void
	{
		$this->searchParamsBuilder->setSort(['name' => ['order' => 'desc']]);
		$this->searchParamsBuilder->setFrom(0);
		$this->searchParamsBuilder->setSize(20);
		$this->searchParamsBuilder->addPhrasePrefixQuery('name', 'john');
		$this->searchParamsBuilder->addMatchQuery('name', 'john');
		$this->searchParamsBuilder->addBooleanMatchQuery('status', ['active', 'disabled']);
		$this->searchParamsBuilder->addRangeQuery('score', 8, 64);
		$this->searchParamsBuilder->addIdsQuery([0, 1, 1, 2, 3, 5, 8]);

		Assert::same(
			[
				'index' => 'users',
				'body' => [
					'sort' => ['name' => ['order' => 'desc']],
					'from' => 0,
					'size' => 20,
					'query' => [
						'bool' => [
							'must' => [
								[
									'multi_match' => [
										'query' => 'john',
										'type' => 'phrase_prefix',
										'fields' => ['name'],
									],
								],
								[
									'match' => ['name' => ['query' => 'john']],
								],
								[
									'bool' => [
										'should' => [
											[
												[
													'match' => ['status' => ['query' => 'active']],
												],
												[
													'match' => ['status' => ['query' => 'disabled']],
												],
											],
										],
									],
								],
								[
									'range' => ['score' => ['gte' => 8, 'lte' => 64]],
								],
								[
									'ids' => [
										'values' => [0, 1, 1, 2, 3, 5, 8],
									],
								],
							],
						],
					],
				],
			],
			$this->searchParamsBuilder->buildParams()
		);
	}

}


$test_case = new SearchParamsBuilderTest();
$test_case->run();
