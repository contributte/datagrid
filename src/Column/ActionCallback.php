<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Column;

use Contributte\Datagrid\Datagrid;
use Nette\SmartObject;

/**
 * @method void onClick(mixed $id)
 */
class ActionCallback extends Action
{

	use SmartObject;

	/** @var callable[] */
	public array $onClick;

	/**
	 * Create link to datagrid::handleActionCallback() to fire custom callback
	 */
	protected function createLink(Datagrid $grid, string $href, array $params): string
	{
		/**
		 * Int case of ActionCallback, $this->href is a identifier of user callback
		 */
		$params += ['__key' => $this->href];

		return $this->grid->link('actionCallback!', $params);
	}

}
