<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Tests\Files;

use Mockery;
use Nette\Application\UI\Presenter;
use Nette\Application\UI\Template;

final class TestPresenter extends Presenter
{

	protected function createComponentGrid(): TestGridControl
	{
		return new TestGridControl();
	}

	protected function createTemplate(?string $class = null): Template
	{
		// @phpstan-ignore-next-line
		return Mockery::mock(Template::class)
			->shouldReceive('getFile')
			->andReturn(__DIR__ . '/template.latte')
			->getMock();
	}

}
