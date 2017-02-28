<?php

namespace Ublaboo\DataGrid\Tests\Files;

use Nette\Application\UI\Presenter;
use Ublaboo\DataGrid\DataGrid;

final class ExportTestingPresenter extends Presenter
{
	/**
	 * @param  string  $name
	 * @return DataGrid
	 */
	protected function createComponentGrid(string $name) : DataGrid
	{
		$grid = new DataGrid(NULL, $name);
		$grid->addExportCsv('export', 'export.csv');

		return $grid;
	}
}
