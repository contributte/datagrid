<?php declare(strict_types = 1);

namespace Ublaboo\DataGrid\Column;

use Nette\SmartObject;

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

	public function __construct(callable $callback, ?callable $condition_callback)
	{
		$this->callback = $callback;
		$this->condition_callback = $condition_callback;
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
		return $this->condition_callback;
	}

}
