<?php

namespace Ublaboo\DataGrid\Tests\Files;

use Nette\Application\PresenterFactory;
use Nette\Http;
use Ublaboo\DataGrid\DataGrid;

class XTestingDataGridFactory
{
	/**
	 * @param  string  $presenterName
	 * @return DataGrid
	 */
	public function createXTestingDataGrid(string $presenterName = 'XTesting') : DataGrid
	{
		$presenterFactory = new PresenterFactory;
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
