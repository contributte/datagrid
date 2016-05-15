<?php

namespace Ublaboo\DataGrid\Tests\Files;

use Nette;
use Ublaboo\DataGrid\DataGrid;

final class XTestingPresenter extends Nette\Application\UI\Presenter
{

	/**
	 * @var bool
	 */
	public $action_handeled = FALSE;


	public function handleDoStuff($id)
	{
		$this->action_handeled = TRUE;
	}


	public function link($destination, $args = array())
	{
		return $destination . '?' . http_build_query($args);
	}


	protected function createRequest($component, $destination, array $args, $mode)
	{
		return ucFirst($component->getName()) . $this->link($destination, $args);
	}

	protected function createComponentGrid($name)
	{
		return new DataGrid($this, $name);
	}

}
