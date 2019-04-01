<?php declare(strict_types = 1);

namespace Ublaboo\DataGrid\Tests\Files;

use Nette;
use Nette\Application\Request;
use Nette\Http;

class XTestingDataGridFactoryRouter
{

	public function createXTestingDataGrid()
	{
		$presenterFactory = new Nette\Application\PresenterFactory();
		$presenterFactory->setMapping(['*' => 'Ublaboo\DataGrid\Tests\Files\*Presenter']);

		$presenter = $presenterFactory->createPresenter('Test');

		$url = new Http\UrlScript('http://localhost/index.php');
		$url->setScriptPath('/index.php');
		$request = new Http\Request($url);
		$response = new Http\Response();
		$session = new Http\Session($request, $response);

		$presenter->autoCanonicalize = false;

		$presenter->injectPrimary(null, $presenterFactory, new Nette\Application\Routers\SimpleRouter(), $request, $response, $session);

		$presenter->run(new Request('Test', Http\Request::GET, []));

		return $presenter->getComponent('grid');
	}

}
