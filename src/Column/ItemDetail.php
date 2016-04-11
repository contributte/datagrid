<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\Column;

use Nette\Utils\Html;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo;
use Ublaboo\DataGrid\Traits;

class ItemDetail
{

	use Traits\ButtonIconTrait;

	/**
	 * (renderer | template | block)
	 * @var string
	 */
	protected $type;

	/**
	 * @var string
	 */
	protected $template;

	/**
	 * @var callable
	 */
	protected $renderer;

	/**
	 * @var string
	 */
	protected $title = 'show';

	/**
	 * @var string
	 */
	protected $class = 'btn btn-xs btn-default ajax';

	/**
	 * @var string
	 */
	protected $icon = 'eye';

	/**
	 * @var string
	 */
	protected $text = '';

	/**
	 * @var DataGrid
	 */
	protected $grid;

	/**
	 * @var string|bool
	 */
	protected $primary_where_column;


	/**
	 * @param DataGrid $grid
	 * @param string   $primary_where_column
	 */
	public function __construct(DataGrid $grid, $primary_where_column)
	{
		$this->grid = $grid;
		$this->primary_where_column = $primary_where_column;
	}


	/**
	 * Render row item detail button
	 * @param  Row $row
	 * @return Html
	 */
	public function renderButton($row)
	{
		$a = Html::el('a')
			->href($this->grid->link('getItemDetail!', ['id' => $row->getId()]))
			->data('toggle-detail', $row->getId())
			->data('toggle-detail-grid', $this->grid->getName());

		$this->tryAddIcon($a, $this->getIcon(), $this->getText());

		$a->add($this->text);

		if ($this->title) { $a->title($this->title); }
		if ($this->class) { $a->class($this->class); }

		return $a;
	}


	/**
	 * Render item detail
	 * @param  mixed $item
	 * @return mixed
	 */
	public function render($item)
	{
		return call_user_func($this->getRenderer(), $item);
	}


	/**
	 * Get primary column for where clause
	 * @return string|bool
	 */
	public function getPrimaryWhereColumn()
	{
		return $this->primary_where_column;
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
	 * @return string
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
	 * @return string
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
	 * @return string
	 */
	public function getIcon()
	{
		return $this->icon;
	}


	/**
	 * Set text
	 * @param string $text
	 */
	public function setText($text)
	{
		$this->text = $text;

		return $this;
	}


	/**
	 * Get text
	 * @return string
	 */
	public function getText()
	{
		return $this->text;
	}


	/**
	 * Set item detail type
	 * @param string $type
	 */
	public function setType($type)
	{
		$this->type = (string) $type;

		return $this;
	}


	/**
	 * Get item detail type
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}


	/**
	 * Set item detail template
	 * @param string $template
	 */
	public function setTemplate($template)
	{
		$this->template = (string) $template;

		return $this;
	}


	/**
	 * Get item detail template
	 * @return string
	 */
	public function getTemplate()
	{
		return $this->template;
	}


	/**
	 * Set item detail renderer
	 * @param callable $renderer
	 */
	public function setRenderer($renderer)
	{
		$this->renderer = $renderer;

		return $this;
	}


	/**
	 * Get item detail renderer
	 * @return callable
	 */
	public function getRenderer()
	{
		return $this->renderer;
	}

}
