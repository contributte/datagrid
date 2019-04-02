<?php declare(strict_types = 1);

namespace Ublaboo\DataGrid\Tests\Cases\DataSources;

use Doctrine\Common\Collections\ArrayCollection;
use Ublaboo;

require __DIR__ . '/BaseDataSourceTest.phpt';

final class DoctrineCollectionDataSourceTest extends BaseDataSourceTest
{

	public function setUp(): void
	{
		$this->ds = new Ublaboo\DataGrid\DataSource\DoctrineCollectionDataSource(new ArrayCollection($this->data), 'id');
		$factory = new Ublaboo\DataGrid\Tests\Files\TestingDataGridFactory();
		$this->grid = $factory->createTestingDataGrid();
	}

}
$test_case = new DoctrineCollectionDataSourceTest();
$test_case->run();
