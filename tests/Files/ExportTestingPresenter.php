<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Tests\Files;

use Contributte\Datagrid\Datagrid;
use Nette\Application\UI\Presenter;

final class ExportTestingPresenter extends Presenter
{

	protected function createComponentGrid(string $name): Datagrid
	{
		$grid = new Datagrid(null, $name);
		$grid->addExportCsv('export', 'export.csv');

		return $grid;
	}

}
