<?php declare(strict_types = 1);

namespace Ublaboo\DataGrid\Tests\Files;

use Nette;
use Ublaboo\DataGrid\DataGrid;

final class ExportTestingPresenter extends Nette\Application\UI\Presenter
{

	protected function createComponentGrid($name)
	{
		$grid = new DataGrid(null, $name);
		$grid->addExportCsv('export', 'export.csv');

		return $grid;
	}

}
