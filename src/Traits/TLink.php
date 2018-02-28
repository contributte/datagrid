<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\Traits;

use Nette;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\Presenter;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Exception\DataGridHasToBeAttachedToPresenterComponentException;
use Ublaboo\DataGrid\Exception\DataGridLinkCreationException;

trait TLink
{

	/**
	 * Create link to custom destination
	 * @param  DataGrid $grid
	 * @param  string   $href
	 * @param  array    $params
	 * @return string
	 * @throws DataGridHasToBeAttachedToPresenterComponentException
	 * @throws \InvalidArgumentException
	 * @throws DataGridLinkCreationException
	 */
	protected function createLink(DataGrid $grid, $href, $params)
	{
		$targetComponent = $grid;

		if (strpos($href, ':') !== false) {
			return $grid->getPresenter()->link($href, $params);
		}

		for ($iteration = 0; $iteration < 10; $iteration++) {
			$targetComponent = $targetComponent->getParent();

			if ($targetComponent === null) {
				$this->throwHierarchyLookupException($grid, $href, $params);
			}

			try {
				@$link = $targetComponent->link($href, $params);

			} catch (InvalidLinkException $e) {
				$link = false;
			} catch (Nette\InvalidArgumentException $e) {
				$link = false;
			}

			if ($link) {
				if (strpos($link, '#error') === 0) {
					continue; // Did not find signal handler
				}

				return $link; // Found signal handler!
			} else {
				continue; // Did not find signal handler
			}

			if ($targetComponent instanceof Presenter) {
				// Went the whole way up to the UI\Presenter and did not find any signal handler
				$this->throwHierarchyLookupException($grid, $href, $params);
			}
		}

		// Went 10 steps up to the UI\Presenter and did not find any signal handler
		$this->throwHierarchyLookupException($grid, $href, $params);
	}


	/**
	 * @throws DataGridLinkCreationException
	 */
	private function throwHierarchyLookupException(DataGrid $grid, $href, $params)
	{
		$desiredHandler = get_class($grid->getParent()) . '::handle' . ucfirst($href) . '()';

		throw new DataGridLinkCreationException(
			'DataGrid could not create link "'
			. $href . '" - did not find any signal handler in componenet hierarchy from '
			. get_class($grid->getParent()) . ' up to the '
			. get_class($grid->getPresenter()) . '. '
			. 'Try adding handler ' . $desiredHandler
		);
	}
}
