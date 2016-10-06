<?php

namespace Ublaboo\DataGrid\Tests\Cases\DataSources;
use Doctrine\Common\Collections\ArrayCollection;
use Ublaboo;

require __DIR__ . '/BaseDataSourceTest.phpt';

final class DoctrineCollectionDataSourceTest extends BaseDataSourceTest
{
	public function setUp()
	{

		$this->ds = new Ublaboo\DataGrid\DataSource\DoctrineCollectionDataSource( new ArrayCollection($this->data),'id');
		$factory = new Ublaboo\DataGrid\Tests\Files\XTestingDataGridFactory;
		$this->grid = $factory->createXTestingDataGrid();
	}
}
$test_case = new DoctrineCollectionDataSourceTest();
$test_case->run();
