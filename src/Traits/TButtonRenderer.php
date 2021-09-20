<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\Traits;

use Ublaboo\DataGrid\Column\Renderer;
use Ublaboo\DataGrid\Exception\DataGridColumnRendererException;
use Ublaboo\DataGrid\Exception\DataGridException;
use Ublaboo\DataGrid\Row;

trait TButtonRenderer
{

	/**
	 * @var Renderer|null
	 */
	protected $renderer;

	/**
	 * @var array
	 */
	protected $replacements = [];


	/**
	 * Try to render item with custom renderer
	 * @param  Row|null $row
	 * @return mixed
	 * @throws DataGridColumnRendererException
	 */
	public function useRenderer($row = null)
	{
		$renderer = $this->getRenderer();

		if ($row instanceof Row) {
			$args = [$row->getItem()];
		} else {
			$args = [];
		}

		if (!$renderer) {
			throw new DataGridColumnRendererException;
		}

		if ($renderer->getConditionCallback()) {
			if (!call_user_func_array($renderer->getConditionCallback(), $args)) {
				throw new DataGridColumnRendererException;
			}

			return call_user_func_array($renderer->getCallback(), $args);
		}

		return call_user_func_array($renderer->getCallback(), $args);
	}


	/**
	 * Set renderer callback and (it may be optional - the condition callback will decide)
	 * @param callable $renderer
	 * @throws DataGridException
	 */
	public function setRenderer($renderer, $condition_callback = null)
	{
		if ($this->hasReplacements()) {
			throw new DataGridException(
				'Use either Column::setReplacement() or Column::setRenderer, not both.'
			);
		}

		if (!is_callable($renderer)) {
			throw new DataGridException(
				'Renderer (method Column::setRenderer()) must be callable.'
			);
		}

		if ($condition_callback != null && !is_callable($condition_callback)) {
			throw new DataGridException(
				'Renderer (method Column::setRenderer()) must be callable.'
			);
		}

		$this->renderer = new Renderer($renderer, $condition_callback);

		return $this;
	}


	/**
	 * Set renderer callback just if condition is truthy
	 * @param callable $renderer
	 */
	public function setRendererOnCondition($renderer, $condition_callback)
	{
		return $this->setRenderer($renderer, $condition_callback);
	}


	/**
	 * Return custom renderer callback
	 * @return Renderer|null
	 */
	public function getRenderer()
	{
		return $this->renderer;
	}


	/**
	 * Set column replacements
	 * @param  array $replacements
	 * @return Column
	 */
	public function setReplacement(array $replacements)
	{
		$this->replacements = $replacements;

		return $this;
	}


	/**
	 * Tell whether columns has replacements
	 * @return bool
	 */
	public function hasReplacements()
	{
		return (bool) $this->replacements;
	}


	/**
	 * Apply replacements
	 * @param  Row   $row
	 * @return array
	 */
	public function applyReplacements(Row $row)
	{
		$value = $row->getValue($this->column);

		if ((is_scalar($value) || $value === null) && isset($this->replacements[$value])) {
			return [true, $this->replacements[$value]];
		}

		return [false, null];
	}
}
