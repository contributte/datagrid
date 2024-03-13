<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Tests\Cases;

use Contributte\Datagrid\Datagrid;
use Contributte\Datagrid\Tests\Files\TestingDatagridFactoryRouter;
use Contributte\Datagrid\Traits\TLink;
use Nette\Application\UI\Presenter;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../bootstrap.php';

final class CreateLinkTest extends TestCase
{

	use TLink;

	private Datagrid $grid;

	public function setUp(): void
	{
		$factory = new TestingDatagridFactoryRouter();
		$this->grid = $factory->createTestingDatagrid()->getComponent('grid');
	}

	public function testActionLink(): void
	{
		$this->grid->getPresenter()->invalidLinkMode = Presenter::INVALID_LINK_EXCEPTION;
		$link = $this->createLink($this->grid, 'edit', ['id' => 1]);
		Assert::same('/index.php?id=1&action=edit&presenter=Test', $link);

		$this->grid->getPresenter()->invalidLinkMode = Presenter::INVALID_LINK_EXCEPTION;
		$link = $this->createLink($this->grid, 'edit', ['id' => 1]);
		Assert::same('/index.php?id=1&action=edit&presenter=Test', $link);

		$this->grid->getPresenter()->invalidLinkMode = Presenter::INVALID_LINK_EXCEPTION;
		$link = $this->createLink($this->grid, 'edit', ['id' => 1]);
		Assert::same('/index.php?id=1&action=edit&presenter=Test', $link);

		$this->grid->getPresenter()->invalidLinkMode = Presenter::INVALID_LINK_EXCEPTION;
		$link = $this->createLink($this->grid, 'edit', ['id' => 1]);
		Assert::same('/index.php?id=1&action=edit&presenter=Test', $link);
	}

}


(new CreateLinkTest())->run();
