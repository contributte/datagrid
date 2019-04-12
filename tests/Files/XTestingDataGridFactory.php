<?php

namespace Ublaboo\DataGrid\Tests\Files;

use Nette\Application\Routers\SimpleRouter;
use Nette\Http;
use Nette\Application\PresenterFactory;

class XTestingDataGridFactory
{

	public function createXTestingDataGrid($presenterName = 'XTesting')
	{
		$presenterFactory = new PresenterFactory;
		$presenterFactory->setMapping(['*' => 'Ublaboo\DataGrid\Tests\Files\*Presenter']);

		/* @var XTestingPresenter $presenter */
		$presenter = $presenterFactory->createPresenter($presenterName);
		$presenter->setParent(null, $presenterName);
		$presenter->changeAction('default');

		$url = new Http\UrlScript('http://localhost/');
		$request = new Http\Request($url);
		$response = new Http\Response;
		$session = new Http\Session($request, $response);

		$presenter->injectPrimary(NULL, NULL, new SimpleRouter, $request, $response, $session);

		return $presenter->getComponent('grid');
	}

}
