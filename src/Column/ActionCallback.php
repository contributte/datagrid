<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\Column;

use Nette\Utils\Html;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Row;
use Ublaboo\DataGrid\Exception\DataGridHasToBeAttachedToPresenterComponentException;

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
	 * @var string
	 */
	protected $key;


	/**
	 * @param DataGrid $grid
	 * @param string   $key
	 * @param string   $name
	 * @param array    $params
	 */
	public function __construct(DataGrid $grid, $key, $name, $params)
	{
		$this->grid = $grid;
		$this->key = $key;
		$this->name = $name;
		$this->params = $params;

		$this->class = 'btn btn-xs btn-default';
	}


	/**
	 * Create link to datagrid::handleActionCallback() to fire custom callback
	 * @param  string $href
	 * @param  array  $params
	 * @return string
	 * @throws DataGridHasToBeAttachedToPresenterComponentException
	 * @throws InvalidArgumentException
	 */
	protected function createLink($href, $params)
	{
		$params = $params + ['__key' => $this->key];

		return $this->grid->link('actionCallback!', $params);
	}

}
