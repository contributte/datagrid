<?php

namespace Ublaboo\DataGrid\Tests\Files;

use Nette\Http,
	Nette;

class XTestingDataGridFactoryRouter
{

	public function createXTestingDataGrid()
	{
		$presenterFactory = new Nette\Application\PresenterFactory;
		$presenterFactory->setMapping(['*' => 'Ublaboo\DataGrid\Tests\Files\*Presenter']);

		$presenter = $presenterFactory->createPresenter('Test');

		$url = new Http\UrlScript('http://localhost/index.php');
		$url->setScriptPath('/index.php');
		$request = new Http\Request($url);
		$response = new Http\Response;
		$session = new Http\Session($request, $response);

		$presenter->autoCanonicalize = false;

		$presenter->injectPrimary(NULL, $presenterFactory, new Nette\Application\Routers\SimpleRouter, $request, $response, $session);

		$presenter->run(new \Nette\Application\Request('Test', Http\Request::GET, []));

		return $presenter->getComponent('grid');
	}

}
