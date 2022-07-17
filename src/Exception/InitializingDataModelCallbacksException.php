<?php declare(strict_types = 1);

namespace Ublaboo\DataGrid\Exception;

use Throwable;

class InitializingDataModelCallbacksException extends DataGridException
{
	public function __construct(?Throwable $previous = null)
	{
		parent::__construct("Cannot set callbacks before data model is set.", 0, $previous);
	}
}
