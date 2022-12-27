<?php declare(strict_types = 1);

namespace Ublaboo\DataGrid\Tests\Files;

use Nette\Application\UI\Control;
use Ublaboo\DataGrid\DataGrid;

class TestGridControl extends Control
{

	public function createComponentGrid(): DataGrid
	{
		return new DataGrid();
	}

}
