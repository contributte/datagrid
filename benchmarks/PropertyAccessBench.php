<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Benchmarks;

use Contributte\Datagrid\Utils\PropertyAccessHelper;
use PhpBench\Attributes as Bench;
use stdClass;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * Benchmarks for property access patterns used throughout the Datagrid:
 * - PropertyAccessHelper::getAccessor() lazy singleton (first call vs cached)
 * - Symfony PropertyAccessor getValue() with simple vs nested paths
 * - explode('.', $key) overhead present in every entity property method
 * - Array key access vs object property access vs PropertyAccessor access
 */
class PropertyAccessBench
{

	private PropertyAccessor $accessor;

	private array $arrayItem = [];

	private stdClass $flatObject;

	private stdClass $nestedObject;

	public function setUp(): void
	{
		$this->accessor = PropertyAccessHelper::getAccessor();

		$this->arrayItem = [
			'name' => 'John Doe',
			'email' => 'john@example.com',
			'age' => 30,
		];

		$this->flatObject = new stdClass();
		$this->flatObject->name = 'John Doe';
		$this->flatObject->email = 'john@example.com';
		$this->flatObject->age = 30;

		$country = new stdClass();
		$country->code = 'CZ';
		$country->label = 'Czech Republic';

		$address = new stdClass();
		$address->city = 'Prague';
		$address->country = $country;

		$this->nestedObject = new stdClass();
		$this->nestedObject->name = 'John Doe';
		$this->nestedObject->address = $address;
	}

	// -------------------------------------------------------------------------
	// 1. PropertyAccessHelper::getAccessor() — lazy singleton pattern
	// -------------------------------------------------------------------------

	/**
	 * First call: forces PropertyAccessor creation (resets singleton each iteration)
	 *
	 * @param array{count: int} $params
	 */
	#[Bench\Revs(1000)]
	#[Bench\Iterations(10)]
	#[Bench\ParamProviders('provideAccessCounts')]
	public function benchGetAccessorFirstCall(array $params): void
	{
		for ($i = 0; $i < $params['count']; $i++) {
			// Simulate the cold path: create a new PropertyAccessor each time
			$accessor = PropertyAccess::createPropertyAccessor();
		}
	}

	/**
	 * Subsequent calls: returns cached singleton via PropertyAccessHelper
	 *
	 * @param array{count: int} $params
	 */
	#[Bench\Revs(1000)]
	#[Bench\Iterations(10)]
	#[Bench\ParamProviders('provideAccessCounts')]
	public function benchGetAccessorCached(array $params): void
	{
		for ($i = 0; $i < $params['count']; $i++) {
			$accessor = PropertyAccessHelper::getAccessor();
		}
	}

	// -------------------------------------------------------------------------
	// 2. Symfony PropertyAccessor: simple vs nested property paths
	// -------------------------------------------------------------------------

	/**
	 * PropertyAccessor with a simple (non-dotted) property path: 'name'
	 *
	 * @param array{count: int} $params
	 */
	#[Bench\Revs(1000)]
	#[Bench\Iterations(10)]
	#[Bench\BeforeMethods('setUp')]
	#[Bench\ParamProviders('provideAccessCounts')]
	public function benchPropertyAccessorSimplePath(array $params): void
	{
		$accessor = $this->accessor;
		$object = $this->nestedObject;

		for ($i = 0; $i < $params['count']; $i++) {
			$value = $accessor->getValue($object, 'name');
		}
	}

	/**
	 * PropertyAccessor with a two-level nested path: 'address.city'
	 *
	 * @param array{count: int} $params
	 */
	#[Bench\Revs(1000)]
	#[Bench\Iterations(10)]
	#[Bench\BeforeMethods('setUp')]
	#[Bench\ParamProviders('provideAccessCounts')]
	public function benchPropertyAccessorNestedPath(array $params): void
	{
		$accessor = $this->accessor;
		$object = $this->nestedObject;

		for ($i = 0; $i < $params['count']; $i++) {
			$value = $accessor->getValue($object, 'address.city');
		}
	}

	/**
	 * PropertyAccessor with a three-level nested path: 'address.country.code'
	 *
	 * @param array{count: int} $params
	 */
	#[Bench\Revs(1000)]
	#[Bench\Iterations(10)]
	#[Bench\BeforeMethods('setUp')]
	#[Bench\ParamProviders('provideAccessCounts')]
	public function benchPropertyAccessorDeeplyNestedPath(array $params): void
	{
		$accessor = $this->accessor;
		$object = $this->nestedObject;

		for ($i = 0; $i < $params['count']; $i++) {
			$value = $accessor->getValue($object, 'address.country.code');
		}
	}

	// -------------------------------------------------------------------------
	// 3. explode('.', $key) overhead — present in every entity property method
	// -------------------------------------------------------------------------

	/**
	 * explode() on a simple key with no dots: 'name'
	 *
	 * @param array{count: int} $params
	 */
	#[Bench\Revs(1000)]
	#[Bench\Iterations(10)]
	#[Bench\ParamProviders('provideAccessCounts')]
	public function benchExplodeSimpleKey(array $params): void
	{
		for ($i = 0; $i < $params['count']; $i++) {
			$properties = explode('.', 'name');
		}
	}

	/**
	 * explode() on a two-level dotted key: 'address.city'
	 *
	 * @param array{count: int} $params
	 */
	#[Bench\Revs(1000)]
	#[Bench\Iterations(10)]
	#[Bench\ParamProviders('provideAccessCounts')]
	public function benchExplodeDottedKey(array $params): void
	{
		for ($i = 0; $i < $params['count']; $i++) {
			$properties = explode('.', 'address.city');
		}
	}

	/**
	 * explode() on a three-level dotted key: 'address.country.code'
	 *
	 * @param array{count: int} $params
	 */
	#[Bench\Revs(1000)]
	#[Bench\Iterations(10)]
	#[Bench\ParamProviders('provideAccessCounts')]
	public function benchExplodeDeeplyDottedKey(array $params): void
	{
		for ($i = 0; $i < $params['count']; $i++) {
			$properties = explode('.', 'address.country.code');
		}
	}

	/**
	 * Full explode + array_shift loop as used in getDoctrineEntityProperty()
	 *
	 * @param array{count: int} $params
	 */
	#[Bench\Revs(1000)]
	#[Bench\Iterations(10)]
	#[Bench\BeforeMethods('setUp')]
	#[Bench\ParamProviders('provideAccessCounts')]
	public function benchExplodeWithShiftLoop(array $params): void
	{
		$accessor = $this->accessor;
		$object = $this->nestedObject;

		for ($i = 0; $i < $params['count']; $i++) {
			$properties = explode('.', 'address.country.code');
			$value = $object;

			while ($property = array_shift($properties)) {
				if (!is_object($value)) {
					break;
				}

				$value = $accessor->getValue($value, $property);
			}
		}
	}

	// -------------------------------------------------------------------------
	// 4. Array key access vs object property access vs PropertyAccessor
	// -------------------------------------------------------------------------

	/**
	 * Direct array key access: $item[$key]
	 *
	 * @param array{count: int} $params
	 */
	#[Bench\Revs(1000)]
	#[Bench\Iterations(10)]
	#[Bench\BeforeMethods('setUp')]
	#[Bench\ParamProviders('provideAccessCounts')]
	public function benchArrayKeyAccess(array $params): void
	{
		$item = $this->arrayItem;
		$keys = ['name', 'email', 'age'];

		for ($i = 0; $i < $params['count']; $i++) {
			$key = $keys[$i % 3];
			$value = $item[$key];
		}
	}

	/**
	 * Direct object property access: $item->{$key}
	 *
	 * @param array{count: int} $params
	 */
	#[Bench\Revs(1000)]
	#[Bench\Iterations(10)]
	#[Bench\BeforeMethods('setUp')]
	#[Bench\ParamProviders('provideAccessCounts')]
	public function benchObjectPropertyAccess(array $params): void
	{
		$item = $this->flatObject;
		$keys = ['name', 'email', 'age'];

		for ($i = 0; $i < $params['count']; $i++) {
			$key = $keys[$i % 3];
			$value = $item->{$key};
		}
	}

	/**
	 * Symfony PropertyAccessor access: $accessor->getValue($item, $key)
	 *
	 * @param array{count: int} $params
	 */
	#[Bench\Revs(1000)]
	#[Bench\Iterations(10)]
	#[Bench\BeforeMethods('setUp')]
	#[Bench\ParamProviders('provideAccessCounts')]
	public function benchPropertyAccessorAccess(array $params): void
	{
		$accessor = $this->accessor;
		$item = $this->flatObject;
		$keys = ['name', 'email', 'age'];

		for ($i = 0; $i < $params['count']; $i++) {
			$key = $keys[$i % 3];
			$value = $accessor->getValue($item, $key);
		}
	}

	/**
	 * PropertyAccessHelper::getValue() static wrapper (includes singleton lookup)
	 *
	 * @param array{count: int} $params
	 */
	#[Bench\Revs(1000)]
	#[Bench\Iterations(10)]
	#[Bench\BeforeMethods('setUp')]
	#[Bench\ParamProviders('provideAccessCounts')]
	public function benchPropertyAccessHelperStaticAccess(array $params): void
	{
		$item = $this->flatObject;
		$keys = ['name', 'email', 'age'];

		for ($i = 0; $i < $params['count']; $i++) {
			$key = $keys[$i % 3];
			$value = PropertyAccessHelper::getValue($item, $key);
		}
	}

	// -------------------------------------------------------------------------
	// Param providers
	// -------------------------------------------------------------------------

	/**
	 * @return array<string, array{count: int}>
	 */
	public function provideAccessCounts(): array
	{
		return [
			'10 accesses' => ['count' => 10],
			'50 accesses' => ['count' => 50],
			'200 accesses' => ['count' => 200],
		];
	}

}
