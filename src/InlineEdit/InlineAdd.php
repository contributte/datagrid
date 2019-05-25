<?php

declare(strict_types=1);

namespace Ublaboo\DataGrid\InlineEdit;

/**
 * @method onSubmit(Nette\Utils\ArrayHash $values)
 */
class InlineAdd extends InlineEdit
{

	/**
	 * @var bool
	 */
	private $shouldBeRendered = false;


	public function shouldBeRendered(): bool
	{
		return $this->shouldBeRendered;
	}
	
	
	public function setShouldBeRendered(bool $shouldBeRendered): void
	{
		$this->shouldBeRendered = $shouldBeRendered;
	}
}
