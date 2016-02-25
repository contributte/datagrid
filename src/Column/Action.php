<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\Column;

use Nette\Utils\Html;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Exception\DataGridHasToBeAttachedToPresenterComponentException;
use Ublaboo\DataGrid\Exception\DataGridException;
use Ublaboo\DataGrid\Row;

class Action extends Column
{

	/**
	 * @var string|callable
	 */
	protected $title;

	/**
	 * @var string|callable
	 */
	protected $class;

	/**
	 * @var string|callable
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
	 * @var array|callable
	 */
	protected $confirm;

	/**
	 * @var array
	 */
	protected $data_attributes = [];


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

		$link = $this->createLink($this->href, $this->getItemParams($row));

		$a = Html::el('a')->href($link);

		if ($this->icon) {
			$a->add(Html::el('span')->class(DataGrid::$icon_prefix.$this->getIcon($row)));

			if (strlen($this->name)) {
				$a->add('&nbsp;');
			}
		}

		if ($this->data_attributes) {
			foreach ($this->data_attributes as $key => $value) {
				$a->data($key, $value);
			}
		}

		$a->add($this->translate($this->name));

		if ($this->title) { $a->title($this->translate($this->getTitle($row))); }
		if ($this->class) { $a->class($this->getClass($row)); }
		if ($confirm = $this->getConfirm($row)) { $a->data('confirm', $this->translate($confirm)); }

		return $a;
	}


	/**
	 * Set attribute title
	 * @param string|callable $title
	 * @return static
	 */
	public function setTitle($title)
	{
		if (!is_string($title) && !is_callable($title) && !is_null($title)) {
			throw new DataGridException(
				'Action title has to be either string or callback, that will return string'
			);
		}

		$this->title = $title;

		return $this;
	}


	/**
	 * Get attribute title
	 * @param Row $row
	 * @return string
	 */
	public function getTitle(Row $row)
	{
		/**
		 * If user callback was used for setting action title, it has to return string
		 */
		if (is_callable($this->title)) {
			$title = call_user_func($this->title, $row->getItem());

			if (!is_string($title)) {
				throw new DataGridException('Action class callback has to return string');
			}

			return $title;
		}

		return $this->title;
	}


	/**
	 * Set attribute class
	 * @param string|callable $class
	 * @return static
	 */
	public function setClass($class)
	{
		if (!is_string($class) && !is_callable($class) && !is_null($class)) {
			throw new DataGridException(
				'Action class has to be either string or callback, that will return string'
			);
		}

		$this->class = $class;

		return $this;
	}


	/**
	 * Get attribute class
	 * @param Row $row
	 * @return string
	 */
	public function getClass(Row $row)
	{
		/**
		 * If user callback was used for setting action class, it has to return string
		 */
		if (is_callable($this->class)) {
			$class = call_user_func($this->class, $row->getItem());

			if (!is_string($class)) {
				throw new DataGridException('Action class callback has to return string');
			}

			return $class;
		}

		return $this->class;
	}


	/**
	 * Set icon
	 * @param string|callable $icon
	 * @return static|callable
	 */
	public function setIcon($icon)
	{
		if (!is_string($icon) && !is_callable($icon) && !is_null($icon)) {
			throw new DataGridException(
				'Action icon has to be either string or callback, that will return string'
			);
		}

		$this->icon = $icon;

		return $this;
	}


	/**
	 * Get icon
	 * @param Row $row
	 * @return string
	 */
	public function getIcon(Row $row)
	{
		/**
		 * If user callback was used for setting action icon, it has to return string
		 */
		if (is_callable($this->icon)) {
			$icon = call_user_func($this->icon, $row->getItem());

			if (!is_string($icon)) {
				throw new DataGridException('Action icon callback has to return string');
			}

			return $icon;
		}

		return $this->icon;
	}


	/**
	 * Set confirm dialog
	 * @param string|callable $message
	 * @param string $column
	 * @return static
	 */
	public function setConfirm($message, $column = NULL)
	{
		if (!is_string($message) && !is_callable($message) && !is_null($message)) {
			throw new DataGridException(
				'Action message has to be either string or callback, that will return string'
			);
		}

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

		$question = $this->confirm[0];

		/**
		 * If user callback was used for setting action confirmation dialog, it has to return string
		 */
		if (is_callable($question)) {
			$question = call_user_func($question, $row->getItem());

			if (!is_string($question)) {
				throw new DataGridException('Action confirmation dialog callback has to return string');
			}
		}

		if (!$this->confirm[1]) {
			return $question;
		}

		return str_replace('%s', $row->getValue($this->confirm[1]), $question);
	}


	/**
	 * Setting data attributes
	 * @param string $key
	 * @param mixed $value
	 */
	public function setDataAttribute($key, $value)
	{
		$this->data_attributes[$key] = $value;
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


	/**
	 * Translator helper
	 * @param  string $message
	 * @return string
	 */
	protected function translate($message)
	{
		return $this->grid->getTranslator()->translate($message);
	}

}
