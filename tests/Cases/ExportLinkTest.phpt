<?php

namespace Ublaboo\DataGrid\Tests\Cases;

use Nette\Application\AbortException;
use Tester\Assert;
use Tester\TestCase;
use Ublaboo;
use Ublaboo\DataGrid\Tests\Files\XTestingDataGridFactory;



require __DIR__ . '/../bootstrap.php';

final class ExportLinkTest extends TestCase
{

	public function testExportLink()
	{
		$factory = new XTestingDataGridFactory;
		$grid = $factory->createXTestingDataGrid('ExportTesting');
		$grid->setDataSource([]);

		Assert::exception(function () use ($grid) {
			$grid->handleExport(1);
		}, AbortException::class);
	}

}

$test_case = new ExportLinkTest;
$test_case->run();
