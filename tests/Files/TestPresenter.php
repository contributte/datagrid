<?php declare(strict_types = 1);

namespace Ublaboo\DataGrid\Tests\Files;

use Nette\Application\UI\Presenter;

final class TestPresenter extends Presenter
{

	protected function createComponentGrid()
	{
		return new TestGridControl();
	}

	protected function createTemplate($class = null): void
	{
	}

}
