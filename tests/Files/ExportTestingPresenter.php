<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Tests\Files;

use Contributte\Datagrid\Datagrid;
use Nette\Application\UI\Presenter;

final class ExportTestingPresenter extends Presenter
{

	protected function createComponentDatagrid(string $name): Datagrid
	{
		$datagrid = new Datagrid(null, $name);
		$datagrid->addExportCsv('export', 'export.csv');

		return $datagrid;
	}

}
