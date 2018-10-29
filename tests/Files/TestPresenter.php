<?php

namespace Ublaboo\DataGrid\Tests\Files;

use Ublaboo\DataGrid\DataGrid;

final class TestPresenter extends \Nette\Application\UI\Presenter
{

	protected function createComponentGrid($name)
	{
		$factory = new TestGridControl();

		return $factory;
	}

	protected function createTemplate($class = null)
	{
	}

}