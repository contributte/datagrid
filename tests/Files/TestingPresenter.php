<?php

declare(strict_types=1);

namespace Ublaboo\DataGrid\Tests\Files;

use Nette\Application\UI\Presenter;
use Ublaboo\DataGrid\DataGrid;

final class TestingPresenter extends Presenter
{

	/**
	 * @var bool
	 */
	public $actionHandeled = false;

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


	/*protected function createRequest(
		Component $component,
		string $destination,
		array $args,
		string $mode
	): ?string
	{
		return ucfirst($component->getName()) . $this->link($destination, $args);
	}*/

	protected function createComponentGrid(string $name): DataGrid
	{
		return new DataGrid($this, $name);
	}
}
