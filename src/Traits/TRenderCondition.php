<?php declare(strict_types=1);

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\Traits;

use Ublaboo\DataGrid\Row;

trait TRenderCondition
{

	/**
	 * @var callable|null
	 */
	protected $renderConditionCallback;


	public function setRenderCondition(callable $condition): self
	{
		$this->renderConditionCallback = $condition;

		return $this;
	}


	public function shouldBeRendered(Row $row): bool
	{
		$condition = $this->renderConditionCallback;

		return $condition ? $condition($row->getItem()) : true;
	}

}
