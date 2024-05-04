<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Tests\Files;

use Contributte\Datagrid\Datagrid;
use Nette\Application\PresenterFactory;
use Nette\Application\UI\Presenter;
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

		/** @var Presenter $presenter */
		$presenter = $presenterFactory->createPresenter($presenterName);

		$url = new UrlScript('http://localhost');
		$request = new Request($url);
		$response = new Response();
		$session = new Session($request, $response);

		$presenter->injectPrimary($request, $response, $presenterFactory, null, $session);

		return $presenter->getComponent('grid');
	}

}
