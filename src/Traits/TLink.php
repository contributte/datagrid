<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Traits;

use Contributte\Datagrid\Datagrid;
use Contributte\Datagrid\Exception\DatagridHasToBeAttachedToPresenterComponentException;
use Contributte\Datagrid\Exception\DatagridLinkCreationException;
use InvalidArgumentException;
use Nette\Application\UI\Component;
use Nette\Application\UI\InvalidLinkException;
use Nette\Application\UI\Presenter;
use UnexpectedValueException;

trait TLink
{

	/**
	 * @throws DatagridHasToBeAttachedToPresenterComponentException
	 * @throws InvalidArgumentException
	 * @throws DatagridLinkCreationException
	 * @throws UnexpectedValueException
	 */
	protected function createLink(
		Datagrid $grid,
		string $href,
		array $params
	): string
	{
		$targetComponent = $grid;

		$presenter = $grid->getPresenter();

		if (str_contains($href, ':')) {
			return $presenter->link($href, $params);
		}

		for ($iteration = 0; $iteration < 10; $iteration++) {
			$targetComponent = $targetComponent->getParent();

			if (!$targetComponent instanceof Component) {
				throw $this->createHierarchyLookupException($grid, $href, $params);
			}

			try {
				$link = $targetComponent->link($href, $params);
			} catch (InvalidLinkException) {
				$link = false;
			}

			if (is_string($link)) {
				if (
					str_starts_with($link, '#error') ||
					(strrpos($href, '!') !== false && str_starts_with($link, '#')) ||
					(in_array($presenter->invalidLinkMode, [Presenter::InvalidLinkWarning, Presenter::InvalidLinkSilent], true) && str_starts_with($link, '#'))
				) {
					continue; // Did not find signal handler
				}

				return $link; // Found signal handler!
			} else {
				continue; // Did not find signal handler
			}
		}

		// Went 10 steps up to the Presenter and did not find any signal handler
		throw $this->createHierarchyLookupException($grid, $href, $params);
	}

	private function createHierarchyLookupException(
		Datagrid $grid,
		string $href,
		array $params
	): DatagridLinkCreationException
	{
		$parent = $grid->getParent();
		$presenter = $grid->getPresenter();

		if ($parent === null) {
			throw new UnexpectedValueException(
				sprintf('%s can not live withnout a parent component', self::class)
			);
		}

		$desiredHandler = $parent::class . '::handle' . ucfirst($href) . '()';

		return new DatagridLinkCreationException(
			'Datagrid could not create link "'
			. $href . '" - did not find any signal handler in componenet hierarchy from '
			. $parent::class . ' up to the '
			. $presenter::class . '. '
			. 'Try adding handler ' . $desiredHandler
		);
	}

}
