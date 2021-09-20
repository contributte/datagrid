<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\Column;

use Nette\SmartObject;
use Ublaboo;

class Renderer
{

	use SmartObject;

	/**
	 * @var callable
	 */
	protected $callback;

	/**
	 * @var callable|NULL
	 */
	protected $condition_callback;


	/**
	 * @param callable      $callback
	 * @param callable|NULL $condition_callback
	 */
	public function __construct($callback, $condition_callback)
	{
		$this->callback = $callback;
		$this->condition_callback = $condition_callback;
	}


	/**
	 * Get custom renderer callback
	 * @return callable
	 */
	public function getCallback()
	{
		return $this->callback;
	}


	/**
	 * Get custom renderer condition callback
	 * @return callable|NULL
	 */
	public function getConditionCallback()
	{
		return $this->condition_callback;
	}
}
