<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Tests\Files;

use Contributte\Datagrid\Datagrid;
use Nette\Application\PresenterFactory;
use Nette\Http\Request;
use Nette\Http\Response;
use Nette\Http\Session;
use Nette\Http\UrlScript;

class TestingDatagridFactory
{

	public function createTestingDatagrid(string $presenterName = 'Testing'): Datagrid
	{
		$presenterFactory = new PresenterFactory();
		$presenterFactory->setMapping(['*' => 'Contributte\Datagrid\Tests\Files\*Presenter']);

		$presenter = $presenterFactory->createPresenter($presenterName);

		$url = new UrlScript('http://localhost');
		$request = new Request($url);
		$response = new Response();
		$session = new Session($request, $response);

		$presenter->injectPrimary(null, null, null, $request, $response, $session);

		return $presenter->getComponent('grid');
	}

}
