<?php

namespace Ublaboo\DataGrid\Tests\Files;

use Nette\Http,
	Nette;

class XTestingDataGridFactory
{

	public function createXTestingDataGrid($presenterName = 'XTesting')
	{
		$presenterFactory = new Nette\Application\PresenterFactory;
		$presenterFactory->setMapping(['*' => 'Ublaboo\DataGrid\Tests\Files\*Presenter']);

		$presenter = $presenterFactory->createPresenter($presenterName);

		$url = new Http\UrlScript('localhost');
		$request = new Http\Request($url);
		$response = new Http\Response;
		$session = new Http\Session($request, $response);

		$presenter->injectPrimary(NULL, NULL, NULL, $request, $response, $session);

		return $presenter->getComponent('grid');
	}

}
