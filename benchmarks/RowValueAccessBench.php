<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Benchmarks;

use Closure;
use PhpBench\Attributes as Bench;

/**
 * Benchmarks for Row::getValue() optimization:
 * - Original: repeated instanceof chain on every getValue() call
 * - Optimized: cached closure dispatch resolved once in constructor
 */
class RowValueAccessBench
{

	private array $arrayItem = [];

	/** @var object */
	private object $objectItem;

	private Closure $cachedArrayAccessor;

	private Closure $cachedObjectAccessor;

	private const COLUMNS = ['id', 'name', 'email', 'age', 'city', 'country', 'phone', 'status'];

	public function setUp(): void
	{
		$this->arrayItem = [
			'id' => 1,
			'name' => 'John Doe',
			'email' => 'john@example.com',
			'age' => 30,
			'city' => 'Prague',
			'country' => 'CZ',
			'phone' => '+420123456789',
			'status' => 'active',
		];

		$this->objectItem = (object) $this->arrayItem;

		// Cached accessor for array items (optimized pattern)
		$item = $this->arrayItem;
		$this->cachedArrayAccessor = static function (string $key) use ($item): mixed {
			return $item[$key];
		};

		// Cached accessor for object items (optimized pattern)
		$obj = $this->objectItem;
		$this->cachedObjectAccessor = static function (string $key) use ($obj): mixed {
			return $obj->{$key};
		};
	}

	/**
	 * Original: instanceof chain for array items (checks 5 types before reaching array branch)
	 *
	 * @param array{columns: int} $params
	 */
	#[Bench\Revs(1000)]
	#[Bench\Iterations(10)]
	#[Bench\BeforeMethods('setUp')]
	#[Bench\ParamProviders('provideColumnCounts')]
	public function benchInstanceofChainArray(array $params): void
	{
		$item = $this->arrayItem;

		for ($i = 0; $i < $params['columns']; $i++) {
			$key = self::COLUMNS[$i % 8];
			// Simulates the original instanceof chain for array items
			// In real code: Entity, NextrasEntity, DibiRow, ActiveRow, NetteRow are checked first
			if (false) { // Entity
			} elseif (false) { // NextrasEntity
			} elseif (false) { // DibiRow
			} elseif (false) { // ActiveRow
			} elseif (false) { // NetteRow
			} elseif (is_array($item)) {
				$value = $item[$key];
			}
		}
	}

	/**
	 * Optimized: cached closure dispatch for array items
	 *
	 * @param array{columns: int} $params
	 */
	#[Bench\Revs(1000)]
	#[Bench\Iterations(10)]
	#[Bench\BeforeMethods('setUp')]
	#[Bench\ParamProviders('provideColumnCounts')]
	public function benchCachedClosureArray(array $params): void
	{
		$accessor = $this->cachedArrayAccessor;

		for ($i = 0; $i < $params['columns']; $i++) {
			$key = self::COLUMNS[$i % 8];
			$value = $accessor($key);
		}
	}

	/**
	 * Original: instanceof chain for generic object items (falls through to default)
	 *
	 * @param array{columns: int} $params
	 */
	#[Bench\Revs(1000)]
	#[Bench\Iterations(10)]
	#[Bench\BeforeMethods('setUp')]
	#[Bench\ParamProviders('provideColumnCounts')]
	public function benchInstanceofChainObject(array $params): void
	{
		$item = $this->objectItem;

		for ($i = 0; $i < $params['columns']; $i++) {
			$key = self::COLUMNS[$i % 8];
			// Simulates falling through all instanceof checks to default (Doctrine path)
			if (false) { // Entity
			} elseif (false) { // NextrasEntity
			} elseif (false) { // DibiRow
			} elseif (false) { // ActiveRow
			} elseif (false) { // NetteRow
			} elseif (is_array($item)) { // not array
			} else {
				$value = $item->{$key};
			}
		}
	}

	/**
	 * Optimized: cached closure dispatch for generic object items
	 *
	 * @param array{columns: int} $params
	 */
	#[Bench\Revs(1000)]
	#[Bench\Iterations(10)]
	#[Bench\BeforeMethods('setUp')]
	#[Bench\ParamProviders('provideColumnCounts')]
	public function benchCachedClosureObject(array $params): void
	{
		$accessor = $this->cachedObjectAccessor;

		for ($i = 0; $i < $params['columns']; $i++) {
			$key = self::COLUMNS[$i % 8];
			$value = $accessor($key);
		}
	}

	/**
	 * @return array<string, array{columns: int}>
	 */
	public function provideColumnCounts(): array
	{
		return [
			'8 columns' => ['columns' => 8],
			'20 columns' => ['columns' => 20],
			'50 columns' => ['columns' => 50],
		];
	}

}
