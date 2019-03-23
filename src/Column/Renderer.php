<?php declare(strict_types=1);

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\Column;

use Ublaboo;

class Renderer
{

	/**
	 * @var callable
	 */
	protected $callback;

	/**
	 * @var callable|null
	 */
	protected $conditionCallback;


	public function __construct(
		callable $callback,
		?callable $conditionCallback
	) {
		$this->callback = $callback;
		$this->conditionCallback = $conditionCallback;
	}


	/**
	 * Get custom renderer callback
	 */
	public function getCallback(): callable
	{
		return $this->callback;
	}


	/**
	 * Get custom renderer condition callback
	 */
	public function getConditionCallback(): ?callable
	{
		return $this->conditionCallback;
	}
}
