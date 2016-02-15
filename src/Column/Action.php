<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\Column;

use Nette\Utils\Html;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\DataGridHasToBeAttachedToPresenterComponentException;
use Ublaboo\DataGrid\Row;

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
	 * @var DataGrid
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
	 * @var callable|NULL
	 */
	protected $disable_callback;


	/**
	 * @param DataGrid $grid
	 * @param string   $href
	 * @param string   $name
	 * @param array    $params
	 */
	public function __construct(DataGrid $grid, $href, $name, $params)
	{
		$this->grid = $grid;
		$this->href = $href;
		$this->name = $name;
		$this->params = $params;

		$this->class = 'btn btn-xs btn-default';
	}


	/**
	 * Render row item into template
	 * @param  Row   $row
	 * @return mixed
	 */
	public function render(Row $row)
	{
		/**
		 * Disable function may be used (user may not have permission to see this action)
		 */
		if ($this->disable_callback && call_user_func_array($this->disable_callback, [$row->getItem()])) {
			return;
		}
		
		/**
		 * Renderer function may be used
		 */
		if ($renderer = $this->getRenderer()) {
			if (!$renderer->getConditionCallback()) {
				return call_user_func_array($renderer->getCallback(), [$row->getItem()]);
			}

			if (call_user_func_array($this->getRenderer(), [$row->getItem()])) {
				return call_user_func_array($renderer->getCallback(), [$row->getItem()]);
			}
		}

		try {
			$parent = $this->grid->getParent();
		} catch (DataGridHasToBeAttachedToPresenterComponentException $e) {
			$parent = $this->grid->getPresenter();
		}

		$a = Html::el('a')
			->href($parent->link($this->href, $this->getItemParams($row)));

		if ($this->icon) {
			$a->add(Html::el('span')->class(DataGrid::$icon_prefix.$this->icon));

			if (strlen($this->name)) {
				$a->add('&nbsp;');
			}
		}

		$a->add($this->name);

		if ($this->title) { $a->title($this->title); }
		if ($this->class) { $a->class($this->class); }
		if ($confirm = $this->getConfirm($row)) { $a->data('confirm', $confirm); }

		return $a;
	}


	/**
	 * Set attribute title
	 * @param string $title
	 * @return static
	 */
	public function setTitle($title)
	{
		$this->title = $title;

		return $this;
	}


	/**
	 * Get attribute title
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
	}


	/**
	 * Set attribute class
	 * @param string $class
	 * @return static
	 */
	public function setClass($class)
	{
		$this->class = $class;

		return $this;
	}


	/**
	 * Get attribute class
	 * @return string
	 */
	public function getClass()
	{
		return $this->class;
	}


	/**
	 * Set icon
	 * @param string $icon
	 * @return static
	 */
	public function setIcon($icon)
	{
		$this->icon = $icon;

		return $this;
	}


	/**
	 * Get icon
	 * @return string
	 */
	public function getIcon()
	{
		return $this->icon;
	}


	/**
	 * Set confirm dialog
	 * @param string $message
	 * @param string $column
	 * @return static
	 */
	public function setConfirm($message, $column = NULL)
	{
		$this->confirm = [$message, $column];

		return $this;
	}


	/**
	 * Get confirm dialog for particular row item
	 * @param Row $row
	 * @return string
	 */
	public function getConfirm(Row $row)
	{
		if (!$this->confirm) {
			return NULL;
		}

		if (!$this->confirm[1]) {
			return $this->confirm[0];
		}

		return str_replace('%s', $row->getValue($this->confirm[1]), $this->confirm[0]);
	}
	
	
	
	/**
	 * Get callback for disable
	 * @return callable|NULL
	 */
	public function getDisable() 
	{
		return $this->disable_callback;
	}

	
	/**
	 * Set callback for disable.
	 * If callback returns TRUE, action will not be rendered
	 * @param callable $callback
	 * @return static
	 */
	public function setDisable($callback) 
	{
		$this->disable_callback = $callback;
		return $this;
	}

	

	/**
	 * Get row item params (E.g. action may be called id => $item->id, name => $item->name, ...)
	 * @param  Row   $row
	 * @return array
	 */
	protected function getItemParams(Row $row)
	{
		$return = [];

		foreach ($this->params as $param_name => $param) {
			$return[is_string($param_name) ? $param_name : $param] = $row->getValue($param);
		}

		return $return;
	}

}
