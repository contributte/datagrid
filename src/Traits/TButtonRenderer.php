<?php declare(strict_types = 1);

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
	 *
	 * @return mixed
	 * @throws DataGridColumnRendererException
	 */
	public function useRenderer(?Row $row = null)
	{
		$renderer = $this->getRenderer();

		if ($row instanceof Row) {
			$args = [$row->getItem()];
		} else {
			$args = [];
		}

		if (!$renderer) {
			throw new DataGridColumnRendererException();
		}

		if ($renderer->getConditionCallback()) {
			if (!call_user_func_array($renderer->getConditionCallback(), $args)) {
				throw new DataGridColumnRendererException();
			}

			return call_user_func_array($renderer->getCallback(), $args);
		}

		return call_user_func_array($renderer->getCallback(), $args);
	}


	/**
	 * Set renderer callback and (it may be optional - the condition callback will decide)
	 *
	 * @throws DataGridException
	 */
	public function setRenderer(callable $renderer, $condition_callback = null)
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

		if ($condition_callback !== null && !is_callable($condition_callback)) {
			throw new DataGridException(
				'Renderer (method Column::setRenderer()) must be callable.'
			);
		}

		$this->renderer = new Renderer($renderer, $condition_callback);

		return $this;
	}


	/**
	 * Set renderer callback just if condition is truthy
	 */
	public function setRendererOnCondition(callable $renderer, $condition_callback)
	{
		return $this->setRenderer($renderer, $condition_callback);
	}


	/**
	 * Return custom renderer callback
	 */
	public function getRenderer(): ?Renderer
	{
		return $this->renderer;
	}


	/**
	 * Set column replacements
	 *
	 * @param  array $replacements
	 */
	public function setReplacement(array $replacements): Column
	{
		$this->replacements = $replacements;

		return $this;
	}


	/**
	 * Tell whether columns has replacements
	 */
	public function hasReplacements(): bool
	{
		return (bool) $this->replacements;
	}


	/**
	 * Apply replacements
	 *
	 * @return array
	 */
	public function applyReplacements(Row $row): array
	{
		$value = $row->getValue($this->column);

		if ((is_scalar($value) || $value === null) && isset($this->replacements[$value])) {
			return [true, $this->replacements[$value]];
		}

		return [false, null];
	}

}
