<?php
namespace Ublaboo\DataGrid\Tests\Cases\DataSources;

use Doctrine\Common\Collections\ArrayCollection;
use Ublaboo;
use Ublaboo\DataGrid\DataSource\DoctrineCollectionDataSource;
use Ublaboo\DataGrid\Tests\Files\XTestingDataGridFactory;

require __DIR__ . '/BaseDataSourceTest.phpt';

final class DoctrineCollectionDataSourceTest extends BaseDataSourceTest
{
	public function setUp()
	{
		$this->ds = new DoctrineCollectionDataSource(new ArrayCollection($this->data), 'id');
		$factory = new XTestingDataGridFactory;
		$this->grid = $factory->createXTestingDataGrid();
	}
}
$test_case = new DoctrineCollectionDataSourceTest();
$test_case->run();
