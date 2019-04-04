<?php

declare(strict_types=1);

namespace Ublaboo\DataGrid\Column\Action\Confirmation;

final class CallbackConfirmation implements IConfirmation
{

	/**
	 * @var callable
	 */
	private $callback;


	public function __construct(callable $callback)
	{
		$this->callback = $callback;
	}


	public function getCallback(): callable
	{
		return $this->callback;
	}
}
