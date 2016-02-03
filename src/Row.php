<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid;

use Nette;
use LeanMapper;
use DibiRow;

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


	public function getValue($key)
	{
		if ($this->item instanceof LeanMapper\Entity) {
			return $this->getEntityProperty($this->item, $key);

		} else if ($this->item instanceof DibiRow) {
			return $this->item->{$key};

		} else if ($this->item instanceof Nette\Database\Table\ActiveRow) {
			return $this->item->{$key};

		} else if (is_array($this->item)) {
			return $this->item[$key];

		} else {
			/**
			 * Doctrine entity
			 */
			return $this->getEntityProperty($this->item, $key);

		}
	}


	public function getEntityProperty($item, $key)
	{
		$properties = explode('.', $key);
		$value = $item;

		while ($property = array_shift($properties)) {
			if (!isset($value->{$property})) {
				if ($this->datagrid->strict_entity_property) {
					throw new DataGridException(sprintf(
						'Target Property [%s] is not an object or is empty, trying to get [%s]',
						$value, str_replace('.', '->', $key)
					));
				}

				return NULL;
			}

			$value = $value->{$property};
		}

		return $value;
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
