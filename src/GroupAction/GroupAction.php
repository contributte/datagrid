<?php

/**
 * * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\GroupAction;

use Nette;

class GroupAction extends Nette\Object
{

	/**
	 * @var callable[]
	 */
	public $onSelect = [];

	/**
	 * @var array
	 */
	protected $options;

	/**
	 * @var string
	 */
	protected $title;


	/**
	 * @param string $title
	 * @param array $options
	 */
	public function __construct($title, $options)
	{
		$this->title = $title;
		$this->options = $options;
	}


	/**
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
	}


	/**
	 * @return array
	 */
	public function getOptions()
	{
		return $this->options;
	}


	/**
	 * @return boolean
	 */
	public function hasOptions()
	{
		return (bool) $this->options;
	}

}
