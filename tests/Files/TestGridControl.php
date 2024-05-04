<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Tests\Files;

use Contributte\Datagrid\Datagrid;
use Nette\Application\UI\Control;

class TestGridControl extends Control
{

	public function createComponentDatagrid(): Datagrid
	{
		return new Datagrid();
	}

}
