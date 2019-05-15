<?php

declare(strict_types=1);

namespace Ublaboo\DataGrid\Column;

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
	)
	{
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
