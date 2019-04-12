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

	public function __construct()
	{
		parent::__construct();

		$this->saveGlobalState();
	}


	public function handleDoStuff($id)
	{
		$this->action_handeled = TRUE;
	}

	protected function createComponentGrid($name)
	{
		return new DataGrid($this, $name);
	}

}
