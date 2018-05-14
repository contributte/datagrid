<?php declare(strict_types = 1);

namespace Ublaboo\DataGrid\Traits;

use Ublaboo\DataGrid\Row;

trait TRenderCondition
{

	/**
	 * @var callable|null
	 */
	protected $render_condition_callback;

	/**
	 * @return static
	 */
	public function setRenderCondition(callable $condition)
	{
		$this->render_condition_callback = $condition;

		return $this;
	}


	public function shouldBeRendered(Row $row): bool
	{
		$condition = $this->render_condition_callback;

		return $condition ? $condition($row->getItem()) : true;
	}

}
