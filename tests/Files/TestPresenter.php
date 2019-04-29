<?php

namespace Ublaboo\DataGrid\Tests\Files;

final class TestPresenter extends \Nette\Application\UI\Presenter
{

	protected function createComponentGrid()
	{
		return new TestGridControl();
	}

	protected function createTemplate($class = null)
	{
	}

}
