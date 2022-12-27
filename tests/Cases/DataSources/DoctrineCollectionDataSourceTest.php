<?php declare(strict_types = 1);

namespace Ublaboo\DataGrid\Tests\Cases\DataSources;

use Doctrine\Common\Collections\ArrayCollection;
use Tester\Assert;
use Ublaboo\DataGrid\DataSource\DoctrineCollectionDataSource;
use Ublaboo\DataGrid\Filter\FilterText;
use Ublaboo\DataGrid\Tests\Files\TestingDataGridFactory;

require __DIR__ . '/BaseDataSourceTest.phpt';

final class DoctrineCollectionDataSourceTest extends BaseDataSourceTest
{

	public function setUp(): void
	{
		$this->ds = new DoctrineCollectionDataSource(new ArrayCollection($this->data), 'id');
		$factory = new TestingDataGridFactory();
		$this->grid = $factory->createTestingDataGrid();
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

}

(new DoctrineCollectionDataSourceTest())->run();
