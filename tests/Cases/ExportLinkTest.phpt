<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Tests\Cases;

require __DIR__ . '/../bootstrap.php';

use Contributte\Datagrid\Tests\Files\TestingDatagridFactory;
use Nette\Application\AbortException;
use Tester\Assert;
use Tester\TestCase;

final class ExportLinkTest extends TestCase
{

	public function testExportLink(): void
	{
		$factory = new TestingDatagridFactory();
		$grid = $factory->createTestingDatagrid('ExportTesting');
		$grid->setDataSource([]);

		Assert::exception(function () use ($grid): void {
			$grid->handleExport(1);
		}, AbortException::class);
	}

}

(new ExportLinkTest())->run();
