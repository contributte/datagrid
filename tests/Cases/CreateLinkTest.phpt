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

	private Datagrid $datagrid;

	public function setUp(): void
	{
		$factory = new TestingDatagridFactoryRouter();
		$this->datagrid = $factory->createTestingDatagrid()->getComponent('datagrid');
	}

	public function testActionLink(): void
	{
		$this->datagrid->getPresenter()->invalidLinkMode = Presenter::INVALID_LINK_EXCEPTION;
		$link = $this->createLink($this->datagrid, 'edit', ['id' => 1]);
		Assert::same('/index.php?id=1&action=edit&presenter=Test', $link);

		$this->datagrid->getPresenter()->invalidLinkMode = Presenter::INVALID_LINK_EXCEPTION;
		$link = $this->createLink($this->datagrid, 'edit', ['id' => 1]);
		Assert::same('/index.php?id=1&action=edit&presenter=Test', $link);

		$this->datagrid->getPresenter()->invalidLinkMode = Presenter::INVALID_LINK_EXCEPTION;
		$link = $this->createLink($this->datagrid, 'edit', ['id' => 1]);
		Assert::same('/index.php?id=1&action=edit&presenter=Test', $link);

		$this->datagrid->getPresenter()->invalidLinkMode = Presenter::INVALID_LINK_EXCEPTION;
		$link = $this->createLink($this->datagrid, 'edit', ['id' => 1]);
		Assert::same('/index.php?id=1&action=edit&presenter=Test', $link);
	}

}


(new CreateLinkTest())->run();
