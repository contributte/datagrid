<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\Column;

use Nette\Utils\Html;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Exception\DataGridColumnRendererException;
use Ublaboo\DataGrid\Row;

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
	 * @var array
	 */
	protected $params;

	/**
	 * @var string
	 */
	protected $href;

	/**
	 * @var string
	 */
	protected $icon;

	/**
	 * @var array
	 */
	protected $data_attributes = [];

	/**
	 * @var bool
	 */
	protected $open_in_new_tab = false;


	/**
	 * @var array
	 */
	protected $parameters = [];


	/**
	 * @param DataGrid $grid
	 * @param string $key
	 * @param string $column
	 * @param string $name
	 * @param string $href
	 * @param array  $params
	 */
	public function __construct(DataGrid $grid, $key, $column, $name, $href, $params)
	{
		parent::__construct($grid, $key, $column, $name);

		$this->href = $href;
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
		try {
			return $this->useRenderer($row);
		} catch (DataGridColumnRendererException $e) {
			/**
			 * Do not use renderer
			 */
		}

		$value = parent::render($row);

		if (!$value && !$this->icon) {
			return null;
		}

		$a = Html::el('a')
			->href($this->createLink(
				$this->grid,
				$this->href,
				$this->getItemParams($row, $this->params) + $this->parameters
			));

		if (!empty($this->data_attributes)) {
			foreach ($this->data_attributes as $key => $attr_value) {
				$a->data($key, $attr_value);
			}
		}

		if ($this->open_in_new_tab) {
			$a->addAttributes(['target' => '_blank']);
		}

		if ($this->title) {
			$a->title($this->title);
		}
		if ($this->class) {
			$a->class($this->class);
		}

		$element = $a;

		if ($this->icon) {
			$a->addHtml(Html::el('span')->class(DataGrid::$icon_prefix . $this->icon));

			if (strlen($value)) {
				$a->addHtml('&nbsp;');
			}
		}

		if ($this->isTemplateEscaped()) {
			$a->addText($value);
		} else {
			$a->addHtml($value);
		}

		return $element;
	}


	/**
	 * Add parameters to link
	 * @param array $parameters
	 * @return static
	 */
	public function addParameters(array $parameters)
	{
		$this->parameters = $parameters;

		return $this;
	}


	/**
	 * Set icon before simple link
	 * @param string      $icon
	 * @return ColumnLink
	 */
	public function setIcon($icon = null)
	{
		$this->icon = $icon;

		return $this;
	}


	/**
	 * Setting data attributes
	 * @param string $key
	 * @param mixed  $value
	 * @return static
	 */
	public function setDataAttribute($key, $value)
	{
		$this->data_attributes[$key] = $value;

		return $this;
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
	 * @return $this
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
	 * Open link in new window/tab?
	 * @return boolean
	 */
	public function isOpenInNewTab()
	{
		return $this->open_in_new_tab;
	}


	/**
	 * Set link to open in new tab/window or not
	 * @param bool $open_in_new_tab
	 * @return $this
	 */
	public function setOpenInNewTab($open_in_new_tab = true)
	{
		$this->open_in_new_tab = $open_in_new_tab;
		return $this;
	}
}
