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
use Ublaboo\DataGrid\Exception\DataGridColumnRendererException;
use Ublaboo\DataGrid\Row;
use Ublaboo\DataGrid\Traits;

class Action extends Column
{

	use Traits\ButtonIconTrait;

	/**
	 * @var string
	 */
	public static $data_confirm_attribute_name = 'datagrid-confirm';

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
		try {
			return $this->useRenderer($row);
		} catch (DataGridColumnRendererException $e) {
			/**
			 * Do not use renderer
			 */
		}

		$link = $this->createLink($this->href, $this->getItemParams($row, $this->params));

		$a = Html::el('a')->href($link);

		$this->tryAddIcon($a, $this->getIcon($row), $this->getName());

		if (!empty($this->data_attributes)) {
			foreach ($this->data_attributes as $key => $value) {
				$a->data($key, $value);
			}
		}

		$a->add($this->translate($this->getName()));

		if ($this->title) {
			$a->title($this->translate($this->getTitle($row)));
		}

		if ($this->class) {
			$a->class($this->getClass($row));
		}

		if ($confirm = $this->getConfirm($row)) {
			$a->data(static::$data_confirm_attribute_name, $this->translate($confirm));
		}

		return $a;
	}


	/**
	 * Set attribute title
	 * @param string|callable $title
	 * @return static
	 * @throws DataGridException
	 */
	public function setTitle($title)
	{
		$this->checkPropertyStringOrCallable($title, 'title');

		$this->title = $title;

		return $this;
	}


	/**
	 * Get attribute title
	 * @param Row $row
	 * @return string
	 * @throws DataGridException
	 */
	public function getTitle(Row $row)
	{
		/**
		 * If user callback was used for setting action title, it has to return string
		 */
		return $this->getPropertyStringOrCallableGetString($row, $this->title, 'title');
	}


	/**
	 * Set attribute class
	 * @param string|callable $class
	 * @return static
	 * @throws DataGridException
	 */
	public function setClass($class)
	{
		$this->checkPropertyStringOrCallable($class, 'class');

		$this->class = $class;

		return $this;
	}


	/**
	 * Get attribute class
	 * @param Row $row
	 * @return string
	 * @throws DataGridException
	 */
	public function getClass(Row $row)
	{
		/**
		 * If user callback was used for setting action class, it has to return string
		 */
		return $this->getPropertyStringOrCallableGetString($row, $this->class, 'class');
	}


	/**
	 * Set icon
	 * @param string|callable $icon
	 * @return static|callable
	 * @throws DataGridException
	 */
	public function setIcon($icon)
	{
		$this->checkPropertyStringOrCallable($icon, 'icon');

		$this->icon = $icon;

		return $this;
	}


	/**
	 * Get icon
	 * @param Row $row
	 * @return string
	 * @throws DataGridException
	 */
	public function getIcon(Row $row)
	{
		/**
		 * If user callback was used for setting action icon, it has to return string
		 */
		return $this->getPropertyStringOrCallableGetString($row, $this->icon, 'icon');
	}


	/**
	 * Set confirm dialog
	 * @param string|callable $message
	 * @param string $column
	 * @return static
	 * @throws DataGridException
	 */
	public function setConfirm($message, $column = NULL)
	{
		$this->checkPropertyStringOrCallable($message, 'confirmation message');

		$this->confirm = [$message, $column];

		return $this;
	}


	/**
	 * Get confirm dialog for particular row item
	 * @param Row $row
	 * @return string
	 * @throws DataGridException
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
		$question = $this->getPropertyStringOrCallableGetString($row, $question, 'confirmation dialog');

		if (!$this->confirm[1]) {
			return $question;
		}

		return str_replace('%s', $row->getValue($this->confirm[1]), $question);
	}


	/**
	 * Setting data attributes
	 * @param string $key
	 * @param mixed $value
	 * @return static
	 */
	public function setDataAttribute($key, $value)
	{
		$this->data_attributes[$key] = $value;
		
		return $this;
	}


	/**
	 * Check whether given property is string or callable
	 * @param  mixed $property
	 * @return void
	 * @throws DataGridException
	 */
	protected function checkPropertyStringOrCallable($property, $name)
	{
		if (!is_string($property) && !is_callable($property) && !is_null($property)) {
			throw new DataGridException(
				"Action {$name} has to be either string or a callback returning string"
			);
		}
	}


	/**
	 * Check whether given property is string or callable
	 * 	in that case call callback and check property and return it
	 * @param  Row                  $row
	 * @param  string|callable|null $property
	 * @param  string               $name
	 * @return string
	 * @throws DataGridException
	 */
	public function getPropertyStringOrCallableGetString(Row $row, $property, $name)
	{
		/**
		 * String
		 */
		if (is_string($property)) {
			return $property;
		}

		/**
		 * Callable
		 */
		if (is_callable($property)) {
			$value = call_user_func($property, $row->getItem());

			if (!is_string($value)) {
				throw new DataGridException("Action {$name} callback has to return a string");
			}

			return $value;
		}

		return $property;
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
