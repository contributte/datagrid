<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\Export;

use Ublaboo\DataGrid\DataGrid;
use Nette\Utils\Callback;
use Nette\Utils\Html;
use Ublaboo;
use Ublaboo\DataGrid\Traits;

class Export
{

	use Traits\ButtonIconTrait;

	/**
	 * @var string
	 */
	protected $text;

	/**
	 * @var callable
	 */
	protected $callback;

	/**
	 * @var string
	 */
	protected $icon;

	/**
	 * @var string
	 */
	protected $title;

	/**
	 * @var bool
	 */
	protected $ajax;

	/**
	 * @var bool
	 */
	protected $filtered;

	/**
	 * @var string
	 */
	protected $link;

	/**
	 * @var string
	 */
	protected $class = 'btn btn-sm btn-default';

	/**
	 * @var array
	 */
	protected $columns = [];


	/**
	 * @param string   $text
	 * @param callable $callback
	 * @param boolean    $filtered
	 */
	public function __construct($text, $callback, $filtered)
	{
		$this->text = $text;
		$this->callback = $callback;
		$this->filtered = (bool) $filtered;
		$this->title = $text;
	}


	/**
	 * Render export button
	 * @return Html
	 */
	public function render()
	{
		$a = Html::el('a', [
			'class' => [$this->class],
			'title' => $this->getTitle(),
			'href'  => $this->link
		]);

		$this->tryAddIcon($a, $this->getIcon(), $this->getTitle());

		$a->add($this->text);

		if ($this->isAjax()) {
			$a->class[] = 'ajax';
		}

		return $a;
	}


	/**
	 * Tell export which columns to use when exporting data
	 * @param array $columns
	 * @return self
	 */
	public function setColumns($columns)
	{
		$this->columns = $columns;

		return $this;
	}


	/**
	 * Get columns for export
	 * @return array
	 */
	public function getColumns()
	{
		return $this->columns;
	}


	/**
	 * Export signal url
	 * @param string $link
	 * @return self
	 */
	public function setLink($link)
	{
		$this->link = $link;

		return $this;
	}


	/**
	 * Set button class
	 * @param string $class
	 * @return self
	 */
	public function setClass($class)
	{
		$this->class = $class;

		return $this;
	}


	/**
	 * Set export icon
	 * @param string $icon
	 * @return self
	 */
	public function setIcon($icon)
	{
		$this->icon = $icon;

		return $this;
	}


	/**
	 * Get export icon
	 * @return string
	 */
	public function getIcon()
	{
		return $this->icon;
	}


	/**
	 * Set export title
	 * @param string $title
	 * @return self
	 */
	public function setTitle($title)
	{
		$this->title = $title;

		return $this;
	}


	/**
	 * Get export title
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
	}


	/**
	 * Tell export whether to be called via ajax or not
	 * @param bool $ajax
	 */
	public function setAjax($ajax = TRUE)
	{
		$this->ajax = (bool) $ajax;

		return $this;
	}


	/**
	 * Is export called via ajax?
	 * @return bool
	 */
	public function isAjax()
	{
		return $this->ajax;
	}


	/**
	 * Is export filtered?
	 * @return bool
	 */
	public function isFiltered()
	{
		return $this->filtered;
	}


	/**
	 * Call export callback
	 * @param  array    $data
	 * @param  DataGrid $grid
	 * @return void
	 */
	public function invoke(array $data, DataGrid $grid)
	{
		Callback::invokeArgs($this->callback, [$data, $grid]);
	}

}
