<?php

namespace Ublaboo\DataGrid\Tests\Files;

use Latte;
use Nette\Application\PresenterFactory;
use Nette\Application\Request;
use Nette\Application\Routers\SimpleRouter;
use Nette\Bridges\ApplicationLatte\ILatteFactory;
use Nette\Bridges\ApplicationLatte\TemplateFactory;
use Nette\Http;

class XTestingDataGridFactoryRouter
{

	public function createXTestingDataGrid()
	{
		$presenterFactory = new PresenterFactory;
		$presenterFactory->setMapping(['*' => 'Ublaboo\DataGrid\Tests\Files\*Presenter']);

		$presenter = $presenterFactory->createPresenter('Test');

		$url = new Http\UrlScript('http://localhost/index.php');
		if (method_exists($url, 'setScriptPath')) {
			$url->setScriptPath('/index.php');
		}
		$request = new Http\Request($url);
		$response = new Http\Response;
		$session = new Http\Session($request, $response);

		$presenter->autoCanonicalize = false;

		$templateFactory = new TemplateFactory(new class implements ILatteFactory {
			private $engine;

			public function __construct()
			{
				$this->engine = new Latte\Engine;
			}

			function create() : Latte\Engine
			{
				return $this->engine;
			}
		});

		$presenter->injectPrimary(NULL, $presenterFactory, new SimpleRouter, $request, $response, $session, null, $templateFactory);

		$presenter->run(new Request('Test', Http\Request::GET, []));

		return $presenter->getComponent('grid');
	}

}
