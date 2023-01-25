<?php declare(strict_types = 1);

namespace Ublaboo\DataGrid\Tests\Cases;

use Nette\Application\UI\Presenter;
use Tester\Assert;
use Tester\TestCase;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Tests\Files\TestingDataGridFactoryRouter;
use Ublaboo\DataGrid\Traits\TLink;

require __DIR__ . '/../bootstrap.php';

final class CreateLinkTest extends TestCase
{

	use TLink;

	private DataGrid $grid;

	public function setUp(): void
	{
		$factory = new TestingDataGridFactoryRouter();
		$this->grid = $factory->createTestingDataGrid()->getComponent('grid');
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
