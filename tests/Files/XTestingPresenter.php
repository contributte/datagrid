<?php

namespace Ublaboo\DataGrid\Tests\Files;

use Nette\Application\UI\Presenter;
use Nette\Application\UI\Component;
use Ublaboo\DataGrid\DataGrid;

final class XTestingPresenter extends Presenter
{
	/**
	 * @var bool
	 */
	public $action_handeled = FALSE;


	/**
	 * @param int  $id
	 */
	public function handleDoStuff(int $id)
	{
		$this->action_handeled = TRUE;
	}


	/**
	 * @param  string  $destination
	 * @param  array|mixed  $args
	 * @throws InvalidLinkException
	 */
	public function link(string $destination, $args = []): string
	{
		return $destination . '?' . http_build_query($args);
	}


	/**
	 * @param  Component  $component
	 * @param  string  $destination
	 * @param  array  $args
	 * @param  string  $mode
	 * @return string|NULL
	 */
	protected function createRequest(Component $component, string $destination, array $args, string $mode): ?string
	{
		return ucFirst($component->getName()) . $this->link($destination, $args);
	}


	/**
	 * @param  string  $name
	 * @return DataGrid
	 */
	protected function createComponentGrid(string $name) : DataGrid
	{
		return new DataGrid($this, $name);
	}
}
