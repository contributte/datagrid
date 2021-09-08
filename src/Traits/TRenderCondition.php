<?php

declare(strict_types=1);

namespace Ublaboo\DataGrid\Traits;

use Ublaboo\DataGrid\Row;

trait TRenderCondition
{

	/**
	 * @var callable|null
	 */
	protected $renderConditionCallback;
	/** @var bool  */
	protected $aclCondition = true;


	public function setRenderCondition(callable $condition): self
	{
		$this->renderConditionCallback = $condition;

		return $this;
	}


	public function shouldBeRendered(Row $row): bool
	{
		$condition = is_callable($this->renderConditionCallback) ? ($this->renderConditionCallback)($row->getItem()) : true;
		$aclCondition = true;
		if($this->aclCondition && isset($this->key) && $this->key !== null) {
			$aclConditionCallback = $this->grid->getAclConditionCallback();
			$aclCondition         = is_callable( $aclConditionCallback ) ? ( $aclConditionCallback )( $this->key ) : true;
		}

		return $condition && $aclCondition;
	}

	/**
	 * @param bool $aclCondition
	 *
	 * @return TRenderCondition
	 */
	public function setAclCondition( bool $aclCondition = false ): self {
		$this->aclCondition = $aclCondition;

		return $this;
	}


}
