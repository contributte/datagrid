<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\Column;

use Ublaboo,
	Nette;

class Renderer extends Nette\Object
{

	/**
	 * @var callable
	 */
	protected $callback;

	/**
	 * @var callable|NULL
	 */
	protected $condition_callback;


	public function __construct($callback, $condition_callback)
	{
		$this->callback = $callback;
		$this->condition_callback = $condition_callback;
	}


	public function getCallback()
	{
		return $this->callback;
	}


	public function getConditionCallback()
	{
		return $this->condition_callback;
	}

}
