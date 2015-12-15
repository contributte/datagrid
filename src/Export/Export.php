<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\Export;

use Ublaboo\DataGrid\DataGridException,
	Ublaboo\DataGrid\DataGrid,
	Nette\Utils\Callback,
	Nette\Utils\Html,
	Nette;

class Export extends Nette\Object
{

	/**
	 * @var bool
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


	public function __construct($text, $callback, $filtered)
	{
		$this->text = $text;
		$this->callback = $callback;
		$this->filtered = (bool) $filtered;
		$this->title = $text;
	}


	public function render()
	{
		$a = Html::el('a', [
			'class' => [$this->class],
			'title' => $this->getTitle(),
			'href'  => $this->link
		]);

		if ($this->icon) {
			$a->add(Html::el('span')->class("fa fa-$this->icon"));

			if (strlen($this->text)) {
				$a->add('&nbsp;');
			}
		}

		$a->add($this->text);

		if ($this->isAjax()) {
			$a->class[] = 'ajax';
		}

		return $a;
	}


	public function setColumns($columns)
	{
		$this->columns = $columns;

		return $this;
	}


	public function getColumns()
	{
		return $this->columns;
	}


	public function setLink($link)
	{
		$this->link = $link;

		return $this;
	}


	public function setClass($class)
	{
		$this->class = $class;

		return $this;
	}


	public function setIcon($icon)
	{
		$this->icon = $icon;

		return $this;
	}


	public function getIcon()
	{
		return $this->icon;
	}


	public function setTitle($title)
	{
		$this->title = $title;

		return $this;
	}


	public function getTitle()
	{
		return $this->title;
	}


	public function setAjax($ajax = TRUE)
	{
		$this->ajax = (bool) $ajax;

		return $this;
	}


	public function isAjax()
	{
		return $this->ajax;
	}


	public function isFiltered()
	{
		return $this->filtered;
	}


	public function invoke(array $data, DataGrid $grid)
	{
		Callback::invokeArgs($this->callback, [$data, $grid]);
	}


	public function __call($name, $args)
	{
		$set_method = 'set' . ucfirst($name);

		if (method_exists($this, $set_method)) {
			return Nette\Utils\Callback::invokeArgs([$this, $set_method], $args);
		}
	}

}
