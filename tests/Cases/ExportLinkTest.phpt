<?php declare(strict_types = 1);

namespace Ublaboo\DataGrid\Tests\Cases;

require __DIR__ . '/../bootstrap.php';

use Nette\Application\AbortException;
use Tester\Assert;
use Tester\TestCase;
use Ublaboo\DataGrid\Tests\Files\TestingDataGridFactory;

final class ExportLinkTest extends TestCase
{

	public function testExportLink(): void
	{
		$factory = new TestingDataGridFactory();
		$grid = $factory->createTestingDataGrid('ExportTesting');
		$grid->setDataSource([]);

		Assert::exception(function () use ($grid): void {
			$grid->handleExport(1);
		}, AbortException::class);
	}

}

(new ExportLinkTest())->run();
