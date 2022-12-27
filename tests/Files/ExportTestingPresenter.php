<?php declare(strict_types = 1);

namespace Ublaboo\DataGrid\Tests\Files;

use Nette\Application\UI\Presenter;
use Ublaboo\DataGrid\DataGrid;

final class ExportTestingPresenter extends Presenter
{

	protected function createComponentGrid(string $name): DataGrid
	{
		$grid = new DataGrid(null, $name);
		$grid->addExportCsv('export', 'export.csv');

		return $grid;
	}

}
