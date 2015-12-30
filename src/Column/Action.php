<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\Column;

use Nette\Utils\Html,
	Ublaboo\DataGrid\DataGrid;

class Action extends Column
{

	/**
	 * @var string
	 */
	protected $title;

	/**
	 * @var string
	 */
	protected $class;

	/**
	 * @var string
	 */
	protected $icon;

	/**
	 * @var Giatn\DataGrid\DataGrid
	 */
	protected $grid;

	/**
	 * @var string
	 */
	protected $href;

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var array
	 */
	protected $params;

	/**
	 * @var array
	 */
	protected $confirm;

	/**
	 * @var callable
	 */
	protected $renderer;


	public function __construct($grid, $href, $name, $params)
	{
		$this->grid = $grid;
		$this->href = $href;
		$this->name = $name;
		$this->params = $params;

		$this->class = 'btn btn-xs btn-default';
	}


	public function render($item)
	{
		/**
		 * Renderer function may be used
		 */
		if ($renderer = $this->getRenderer()) {
			return call_user_func_array($renderer, [$item]);
		}

		$a = Html::el('a')
			->href($this->grid->getPresenter()->link($this->href, $this->getItemParams($item)));

		if ($this->icon) {
			$a->add(Html::el('span')->class(DataGrid::$icon_prefix . $this->icon));
			
			if (strlen($this->name)) {
				$a->add('&nbsp;');
			}
		}

		$a->add($this->name);

		if ($this->title) { $a->title($this->title); }
		if ($this->class) { $a->class($this->class); }
		if ($confirm = $this->getConfirm($item)) { $a->data('confirm', $confirm); }

		return $a;
	}


	/**
	 * Set attribute title
	 * @param string $title
	 */
	public function setTitle($title)
	{
		$this->title = $title;

		return $this;
	}


	/**
	 * Get attribute title
	 * @param string $title
	 */
	public function getTitle()
	{
		return $this->title;
	}


	/**
	 * Set attribute class
	 * @param string $class
	 */
	public function setClass($class)
	{
		$this->class = $class;

		return $this;
	}


	/**
	 * Get attribute class
	 * @param string $class
	 */
	public function getClass()
	{
		return $this->class;
	}


	/**
	 * Set icon
	 * @param string $icon
	 */
	public function setIcon($icon)
	{
		$this->icon = $icon;

		return $this;
	}


	/**
	 * Get icon
	 * @param string $icon
	 */
	public function getIcon()
	{
		return $this->icon;
	}


	/**
	 * Set confirm dialog
	 * @param string $messgae
	 * @param string $column
	 */
	public function setConfirm($message, $column = NULL)
	{
		$this->confirm = [$message, $column];

		return $this;
	}


	/**
	 * Get confirm dialog for particular item
	 * @param string $item
	 */
	public function getConfirm($item)
	{
		if (!$this->confirm) {
			return NULL;
		}

		if (!$this->confirm[1]) {
			return $this->confirm[0];
		}

		if (is_object($item)) {
			return str_replace('%s', $item->{$this->confirm[1]}, $this->confirm[0]);
		} else {
			return str_replace('%s', $item[$this->confirm[1]], $this->confirm[0]);
		}
	}


	/**
	 * Get item params (E.g. action may be called id => $item->id, name => $item->name, ...)
	 * @param  mixed $item
	 * @return array
	 */
	protected function getItemParams($item)
	{
		$return = [];

		foreach ($this->params as $param_name => $param) {
			if (is_object($item)) {
				$return[is_string($param_name) ? $param_name : $param] = $item->{$param};
			} else {
				$return[is_string($param_name) ? $param_name : $param] = $item[$param];
			}
		}

		return $return;
	}


	/**
	 * Set renderer callback
	 * @param callable $renderer
	 */
	public function setRenderer($renderer)
	{
		if (!is_callable($renderer)) {
			throw new DataGridException (
				"Renderer (method Column::setRenderer()) must be callable."
			);
		}

		$this->renderer = $renderer;

		return $this;
	}


	/**
	 * Return custom renderer callback
	 * @return callable
	 */
	public function getRenderer()
	{
		return $this->renderer;
	}

}
