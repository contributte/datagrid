<?php

/**
 * @copyright   Copyright (c) 2015 Giant.cz <help@giant.cz>
 * @author      Pavel Janda <pavel.janda@giant.cz>
 * @package     Giant
 */

namespace Ublaboo\DataGrid\Column;

use Nette\Utils\Html;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Row;
use Ublaboo\DataGrid\Exception\DataGridHasToBeAttachedToPresenterComponentException;

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
	 * Render row item into template
	 * @param  Row   $row
	 * @return mixed
	 */
	public function render(Row $row)
	{
		/**
		 * Renderer function may be used
		 */
		if ($renderer = $this->getRenderer()) {
			if (!$renderer->getConditionCallback()) {
				return call_user_func_array($renderer->getCallback(), [$row->getItem()]);
			}

			if (call_user_func_array($renderer->getConditionCallback(), [$row->getItem()])) {
				return call_user_func_array($renderer->getCallback(), [$row->getItem()]);
			}
		}

		$value = parent::render($row);

		$a = Html::el('a')
			->href($this->createLink($this->href, $this->getItemParams($row)))
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
	 */
	public function getClass()
	{
		return $this->class;
	}

	/**
	 * Get item params (E.g. action may be called id => $item->id, name => $item->name, ...)
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
