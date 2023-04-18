<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Tests\Files;

use Contributte\Datagrid\Datagrid;
use Nette\Application\UI\Presenter;

final class TestingPresenter extends Presenter
{

	public bool $actionHandeled = false;

	public function handleDoStuff(int $id): void
	{
		$this->actionHandeled = true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function link(string $destination, $args = []): string
	{
		return $destination . '?' . http_build_query($args);
	}

	protected function createComponentGrid(string $name): Datagrid
	{
		return new Datagrid($this, $name);
	}

}
