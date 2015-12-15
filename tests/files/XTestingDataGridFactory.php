<?php

namespace Ublaboo\DataGrid\Tests\Files;

use Nette\Http,
	Nette,
	Ublaboo\DataGrid\DataGrid;

class XTestingDataGridFactory
{

	public function createXTestingDataGrid()
	{
		$presenterFactory = new Nette\Application\PresenterFactory;
		$presenterFactory->setMapping(['*' => 'Ublaboo\DataGrid\Tests\Files\*Presenter']);

		$presenter = $presenterFactory->createPresenter('XTesting');

		$url = new Http\UrlScript('localhost');
		$request = new Http\Request($url);
		$response = new Http\Response;
		$session = new Http\Session($request, $response);

		$presenter->injectPrimary(NULL, NULL, NULL, $request, $response, $session);

		return new DataGrid($presenter, 'XTestingGrid');
	}

}
