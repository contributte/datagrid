<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\Traits;

use Ublaboo\DataGrid\DataGrid;
use Nette\Utils\Html;
use Ublaboo\DataGrid\Row;

trait TButton
{

	/**
	 * @var string|callable
	 */
	protected $title = '';

	/**
	 * @var string
	 */
	protected $class = 'btn btn-xs btn-default';

	/**
	 * @var string|callable
	 */
	protected $icon;

	/**
	 * @var string
	 */
	protected $text = '';


	/**
	 * Should the element has an icon?
	 * @param  Html            $el
	 * @param  string|null     $icon
	 * @param  string          $name
	 * @return void
	 */
	public function tryAddIcon(Html $el, $icon, $name)
	{
		if ($icon) {
			$el->addHtml(Html::el('span')->class(DataGrid::$icon_prefix.$icon));

			if (strlen($name)) {
				$el->addHtml('&nbsp;');
			}
		}
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

}
