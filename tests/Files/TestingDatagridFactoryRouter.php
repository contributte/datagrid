<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Tests\Files;

use Nette\Application\PresenterFactory;
use Nette\Application\Request;
use Nette\Application\Routers\SimpleRouter;
use Nette\Application\UI\Presenter;
use Nette\ComponentModel\IComponent;
use Nette\Http\Request as HttpRequest;
use Nette\Http\Response;
use Nette\Http\Session;
use Nette\Http\UrlScript;

class TestingDatagridFactoryRouter
{

	public function createTestingDatagrid(): ?IComponent
	{
		$presenterFactory = new PresenterFactory();
		$presenterFactory->setMapping(['*' => 'Contributte\Datagrid\Tests\Files\*Presenter']);

		/** @var Presenter $presenter */
		$presenter = $presenterFactory->createPresenter('Test');

		$url = new UrlScript('http://localhost/index.php');
		$request = new HttpRequest($url);
		$response = new Response();
		$session = new Session($request, $response);

		$presenter->autoCanonicalize = false;

		$presenter->injectPrimary($request, $response, $presenterFactory, new SimpleRouter(), $session);

		$presenter->run(new Request('Test', 'GET', []));

		return $presenter->getComponent('grid');
	}

}
