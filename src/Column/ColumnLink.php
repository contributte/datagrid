<?php

/**
 * @copyright   Copyright (c) 2015 Giant.cz <help@giant.cz>
 * @author      Pavel Janda <pavel.janda@giant.cz>
 * @package     Giant
 */

namespace Ublaboo\DataGrid\Column;

use Nette\Utils\Html,
	Ublaboo\DataGrid\DataGrid;

class ColumnLink extends Column
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
	 * @var DataGrid
	 */
	protected $grid;

	/**
	 * @var array
	 */
	protected $params;

	/**
	 * @var string
	 */
	protected $href;


	/**
	 * @param DataGrid $grid
	 * @param string $column
	 * @param string $name
	 * @param string $href
	 * @param array $params
	 */
	public function __construct(DataGrid $grid, $column, $name, $href, $params)
	{
		parent::__construct($column, $name);

		$this->href   = $href;
		$this->grid   = $grid;
		$this->params = $params;
	}


	/**
	 * Render link for item
	 * @param  mixed $item
	 * @return mixed
	 */
	public function render($item)
	{
		/**
		 * Renderer function may be used
		 */
		if ($renderer = $this->getRenderer()) {
			if (!$renderer->getConditionCallback()) {
				return call_user_func_array($renderer->getCallback(), [$item]);
			}

			if (call_user_func_array($renderer->getConditionCallback(), [$item])) {
				return call_user_func_array($renderer->getCallback(), [$item]);
			}
		}

		$value = parent::render($item);

		$href = $this->grid->getPresenter()->link($this->href, $this->getItemParams($item));
		$a = Html::el('a')
			->href($href)
			->setText($value);

		if ($this->title) { $a->title($this->title); }
		if ($this->class) { $a->class($this->class); }

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

}
