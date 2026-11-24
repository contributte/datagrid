<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Tests\Cases\DataSources;

use Contributte\Datagrid\DataSource\DoctrineCollectionDataSource;
use Contributte\Datagrid\Filter\FilterText;
use Contributte\Datagrid\Tests\Files\TestingDatagridFactory;
use Contributte\Datagrid\Utils\Sorting;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Deprecations\Deprecation;
use Tester\Assert;

require __DIR__ . '/BaseDataSourceTest.phpt';

final class DoctrineCollectionDataSourceTest extends BaseDataSourceTest
{

	public function setUp(): void
	{
		$this->ds = new DoctrineCollectionDataSource(new ArrayCollection($this->data), 'id');
		$factory = new TestingDatagridFactory();
		$this->grid = $factory->createTestingDatagrid();
	}

	public function testFilterMultipleColumns(): void
	{
		$filter = new FilterText($this->grid, 'a', 'b', ['name', 'address']);
		$filter->setValue('lu');
		$this->ds->filter([$filter]);

		Assert::equal([
			$this->data[0],
			$this->data[4],
		], $this->getActualResultAsArray());
	}

	/**
	 * doctrine/collections 3.x accepts only Order enum in Criteria::orderBy(),
	 * 2.x deprecates string directions (doctrine/collections#389).
	 */
	public function testSortPassesOrderEnum(): void
	{
		Deprecation::enableTrackingDeprecations();

		$this->ds->sort(new Sorting(['name' => 'DESC']));
		$this->getActualResultAsArray();

		$this->ds->sort(new Sorting([]));
		$this->getActualResultAsArray();

		Assert::same(0, Deprecation::getTriggeredDeprecations()['https://github.com/doctrine/collections/pull/389'] ?? 0);
	}

}

(new DoctrineCollectionDataSourceTest())->run();
