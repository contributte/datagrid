<?php declare(strict_types = 1);

namespace Ublaboo\DataGrid\Column;

use Ublaboo\DataGrid\DataGrid;

/**
 * @method void onClick(mixed $id)
 */
class ActionCallback extends Action
{

	/**
	 * @var callable
	 */
	public $onClick = [];

	/**
	 * Create link to datagrid::handleActionCallback() to fire custom callback
	 *
	 * @param  array    $params
	 */
	protected function createLink(DataGrid $grid, string $href, array $params): string
	{
		/**
		 * Int case of ActionCallback, $this->href is a identifier of user callback
		 */
		$params = $params + ['__key' => $this->href];

		return $this->grid->link('actionCallback!', $params);
	}

}
