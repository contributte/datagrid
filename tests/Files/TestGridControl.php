<?php

namespace Ublaboo\DataGrid\Tests\Files;


use Nette\Application\UI\Control;
use Ublaboo\DataGrid\DataGrid;

class TestGridControl extends Control
{

	public function createComponentGrid()
	{
		$grid = new DataGrid();

		return $grid;
	}

}