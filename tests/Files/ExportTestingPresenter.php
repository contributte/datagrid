<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Tests\Files;

use Nette\Application\UI\Presenter;
use Contributte\Datagrid\Datagrid;

final class ExportTestingPresenter extends Presenter
{

	protected function createComponentGrid(string $name): Datagrid
	{
		$grid = new Datagrid(null, $name);
		$grid->addExportCsv('export', 'export.csv');

		return $grid;
	}

}
