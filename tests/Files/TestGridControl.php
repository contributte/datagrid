<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Tests\Files;

use Nette\Application\UI\Control;
use Contributte\Datagrid\Datagrid;

class TestGridControl extends Control
{

	public function createComponentGrid(): Datagrid
	{
		return new Datagrid();
	}

}
