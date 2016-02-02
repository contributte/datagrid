<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid;

use Nette;

class Row extends Nette\Object
{

	/**
	 * @var DataGrid
	 */
	protected $datagrid;

	/**
	 * @var mixed
	 */
	protected $item;

	/**
	 * @var string
	 */
	protected $primary_key;

	/**
	 * @var mixed
	 */
	protected $id;


	/**
	 * @param mixed  $item
	 * @param string $primary_key
	 */
	public function __construct(DataGrid $datagrid, $item, $primary_key)
	{
		$this->datagrid = $datagrid;
		$this->item = $item;
		$this->primary_key = $primary_key;

		$this->id = is_object($item) ? $item->{$primary_key} : $item[$primary_key];
	}


	/**
	 * Get id value of item
	 * @return mixed
	 */
	public function getId()
	{
		return $this->id;
	}


	/**
	 * Get original item
	 * @return mixed
	 */
	public function getItem()
	{
		return $this->item;
	}


	public function hasGroupAction()
	{
		$condition = $this->datagrid->getRowCondition('group_action');

		return $condition ? $condition($this->item) : TRUE;
	}


	public function hasAction($key)
	{
		$condition = $this->datagrid->getRowCondition('action', $key);

		return $condition ? $condition($this->item) : TRUE;
	}

}
