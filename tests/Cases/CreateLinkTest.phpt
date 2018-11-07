<?php

namespace Ublaboo\DataGrid\Tests\Cases;

use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\Presenter;
use Tester\TestCase;
use Tester\Assert;
use Mockery;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo;

require __DIR__ . '/../bootstrap.php';

final class CreateLinkTest extends TestCase
{

	use Ublaboo\DataGrid\Traits\TLink;

	/**
	 * @var DataGrid
	 */
	private $grid;


	public function setUp()
	{
		$factory = new Ublaboo\DataGrid\Tests\Files\XTestingDataGridFactoryRouter();
		$this->grid = $factory->createXTestingDataGrid()->getComponent('grid');
	}


	public function testActionLink()
	{
		$this->grid->getPresenter()->invalidLinkMode = Presenter::INVALID_LINK_SILENT;
		Assert::same('#', $this->grid->getPresenter()->link('edit!'));

		$link = $this->createLinkOld($this->grid, 'edit', ['id' => 1]);
		Assert::same('#', $link);

		$this->grid->getPresenter()->invalidLinkMode = Presenter::INVALID_LINK_WARNING;
		$link = $this->createLinkOld($this->grid, 'edit', ['id' => 1]);
		Assert::same('#', $link);

		$this->grid->getPresenter()->invalidLinkMode = Presenter::INVALID_LINK_EXCEPTION;
		$link = $this->createLinkOld($this->grid, 'edit', ['id' => 1]);
		Assert::same('/index.php?id=1&action=edit&presenter=Test', $link);

		$this->grid->getPresenter()->invalidLinkMode = Presenter::INVALID_LINK_TEXTUAL;
		$link = $this->createLinkOld($this->grid, 'edit', ['id' => 1]);
		Assert::same('/index.php?id=1&action=edit&presenter=Test', $link);

		$this->grid->getPresenter()->invalidLinkMode = Presenter::INVALID_LINK_WARNING;
		$link = $this->createLink($this->grid, 'edit', ['id' => 1]);
		Assert::same('/index.php?id=1&action=edit&presenter=Test', $link);

		$this->grid->getPresenter()->invalidLinkMode = Presenter::INVALID_LINK_TEXTUAL;
		$link = $this->createLink($this->grid, 'edit', ['id' => 1]);
		Assert::same('/index.php?id=1&action=edit&presenter=Test', $link);

		$this->grid->getPresenter()->invalidLinkMode = Presenter::INVALID_LINK_EXCEPTION;
		$link = $this->createLink($this->grid, 'edit', ['id' => 1]);
		Assert::same('/index.php?id=1&action=edit&presenter=Test', $link);

		$this->grid->getPresenter()->invalidLinkMode = Presenter::INVALID_LINK_SILENT;
		$link = $this->createLink($this->grid, 'edit', ['id' => 1]);
		Assert::same('/index.php?id=1&action=edit&presenter=Test', $link);


	}

	protected function createLinkOld(DataGrid $grid, $href, $params)
	{
		$targetComponent = $grid;

		if (strpos($href, ':') !== false) {
			return $grid->getPresenter()->link($href, $params);
		}

		for ($iteration = 0; $iteration < 10; $iteration++) {
			$targetComponent = $targetComponent->getParent();

			if ($targetComponent === null) {
				$this->throwHierarchyLookupException($grid, $href, $params);
			}

			try {
				@$link = $targetComponent->link($href, $params);

			} catch (InvalidLinkException $e) {
				$link = false;
			} catch (\InvalidArgumentException $e) {
				$link = false;
			}

			if ($link) {
				if (
					strpos($link, '#error') === 0 ||
					(strrpos($href, "!") !== false && strpos($link, '#') === 0)
				) {
					continue; // Did not find signal handler
				}

				return $link; // Found signal handler!
			} else {
				continue; // Did not find signal handler
			}

		}
	}

}


$test_case = new CreateLinkTest;
$test_case->run();
