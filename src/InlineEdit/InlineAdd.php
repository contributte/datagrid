<?php declare(strict_types = 1);

namespace Contributte\Datagrid\InlineEdit;

use Nette\Utils\ArrayHash;

/**
 * @method onSubmit(ArrayHash $values)
 */
class InlineAdd extends InlineEdit
{

	private bool $shouldBeRendered = false;

	public function shouldBeRendered(): bool
	{
		return $this->shouldBeRendered;
	}

	public function setShouldBeRendered(bool $shouldBeRendered): void
	{
		$this->shouldBeRendered = $shouldBeRendered;
	}

}
