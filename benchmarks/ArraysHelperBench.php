<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Benchmarks;

use PhpBench\Attributes as Bench;

/**
 * Benchmarks for ArraysHelper::testEmpty() optimization:
 * - Original: truthy check + in_array() for falsy-but-meaningful values
 * - Optimized: simplified !== null && !== '' check
 */
class ArraysHelperBench
{

	/**
	 * Original testEmpty implementation
	 *
	 * @param array{data: array<mixed>} $params
	 */
	#[Bench\Revs(5000)]
	#[Bench\Iterations(10)]
	#[Bench\ParamProviders('provideArrays')]
	public function benchTestEmptyOriginal(array $params): void
	{
		$this->testEmptyOriginal($params['data']);
	}

	/**
	 * Optimized testEmpty implementation
	 *
	 * @param array{data: array<mixed>} $params
	 */
	#[Bench\Revs(5000)]
	#[Bench\Iterations(10)]
	#[Bench\ParamProviders('provideArrays')]
	public function benchTestEmptyOptimized(array $params): void
	{
		$this->testEmptyOptimized($params['data']);
	}

	/**
	 * @return array<string, array{data: array<mixed>}>
	 */
	public function provideArrays(): array
	{
		return [
			'empty_strings' => ['data' => ['', '', '', null, '', null, '', '']],
			'with_zero' => ['data' => [0, '', null, '0', false, '', null]],
			'nested_empty' => ['data' => [['', null], ['', [null, '']], '']],
			'nested_with_value' => ['data' => [['', null], ['', [null, 'hello']], '']],
			'all_truthy' => ['data' => ['a', 'b', 'c', 1, 2, 3, true]],
			'large_empty' => ['data' => array_fill(0, 100, '')],
			'large_mixed' => ['data' => array_merge(array_fill(0, 99, ''), ['value'])],
		];
	}

	/**
	 * Original implementation
	 */
	private function testEmptyOriginal(iterable $array): bool
	{
		foreach ($array as $value) {
			if (is_array($value)) {
				if (!$this->testEmptyOriginal($value)) {
					return false;
				}
			} else {
				if ($value) {
					return false;
				}

				if (in_array($value, [0, '0', false], true)) {
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Optimized implementation
	 */
	private function testEmptyOptimized(iterable $array): bool
	{
		foreach ($array as $value) {
			if (is_array($value)) {
				if (!$this->testEmptyOptimized($value)) {
					return false;
				}
			} elseif ($value !== null && $value !== '') {
				return false;
			}
		}

		return true;
	}

}
