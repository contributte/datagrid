<?php

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
	protected $render_condition_callback;


	/**
	 * @param callable $condition
	 * @return static
	 */
	public function setRenderCondition(callable $condition)
	{
		$this->render_condition_callback = $condition;

		return $this;
	}


	/**
	 * @param Row $row
	 * @return bool
	 */
	public function shouldBeRendered(Row $row)
	{
		$condition = $this->render_condition_callback;

		return $condition ? $condition($row->getItem()) : true;
	}

}
