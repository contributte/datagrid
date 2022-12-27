<?php declare(strict_types = 1);

namespace Ublaboo\DataGrid\Tests\Files;

use Mockery;
use Nette\Application\UI\ITemplate;
use Nette\Application\UI\Presenter;

final class TestPresenter extends Presenter
{

	protected function createComponentGrid(): TestGridControl
	{
		return new TestGridControl();
	}

	protected function createTemplate(): ITemplate
	{
		return Mockery::mock(ITemplate::class)
			->shouldReceive('getFile')
			->andReturn(__DIR__ . '/template.latte')
			->getMock();
	}

}
