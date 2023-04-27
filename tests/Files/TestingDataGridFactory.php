<?php declare(strict_types = 1);

namespace Ublaboo\DataGrid\Tests\Files;

use Nette\Application\PresenterFactory;
use Nette\Http\Request;
use Nette\Http\Response;
use Nette\Http\Session;
use Nette\Http\UrlScript;
use Ublaboo\DataGrid\DataGrid;

class TestingDataGridFactory
{

	public function createTestingDataGrid(string $presenterName = 'Testing'): DataGrid
	{
		$presenterFactory = new PresenterFactory();
		$presenterFactory->setMapping(['*' => 'Ublaboo\DataGrid\Tests\Files\*Presenter']);

		$presenter = $presenterFactory->createPresenter($presenterName);

		$url = new UrlScript('http://localhost');
		$request = new Request($url);
		$response = new Response();
		$session = new Session($request, $response);

		$presenter->injectPrimary(null, null, null, $request, $response, $session);

		return $presenter->getComponent('grid');
	}

}
