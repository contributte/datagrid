<?php declare(strict_types = 1);

namespace Ublaboo\DataGrid\Tests\Files;

use Nette\Application\PresenterFactory;
use Nette\Application\Routers\SimpleRouter;
use Nette\ComponentModel\IComponent;
use Nette\Http\Request;
use Nette\Http\Response;
use Nette\Http\Session;
use Nette\Http\UrlScript;

class TestingDataGridFactoryRouter
{

	public function createTestingDataGrid(): ?IComponent
	{
		$presenterFactory = new PresenterFactory();
		$presenterFactory->setMapping(['*' => 'Ublaboo\DataGrid\Tests\Files\*Presenter']);

		$presenter = $presenterFactory->createPresenter('Test');

		$url = new UrlScript('http://localhost/index.php');
		$url->setScriptPath('/index.php');
		$request = new Request($url);
		$response = new Response();
		$session = new Session($request, $response);

		$presenter->autoCanonicalize = false;

		$presenter->injectPrimary(null, $presenterFactory, new SimpleRouter(), $request, $response, $session);

		$presenter->run(new Request('Test', Request::GET, []));

		return $presenter->getComponent('grid');
	}

}
