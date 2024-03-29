<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Traits;

use Contributte\Datagrid\Row;

trait TRenderCondition
{

	/** @var callable|null */
	protected $renderConditionCallback;

	/**
	 * @return static
	 */
	public function setRenderCondition(callable $condition): self
	{
		$this->renderConditionCallback = $condition;

		return $this;
	}

	public function shouldBeRendered(Row $row): bool
	{
		$condition = $this->renderConditionCallback;

		return is_callable($condition)
			? ($condition)($row->getItem())
			: true;
	}

}
