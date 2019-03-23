<?php declare(strict_types=1);

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
	 * @return mixed
	 * @throws DataGridColumnRendererException
	 */
	public function useRenderer(Row $row = null)
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
	 * @throws DataGridException
	 */
	public function setRenderer(
		callable $renderer,
		?callable $conditionCallback = null
	): self
	{
		if ($this->hasReplacements()) {
			throw new DataGridException(
				'Use either Column::setReplacement() or Column::setRenderer, not both.'
			);
		}

		$this->renderer = new Renderer($renderer, $conditionCallback);

		return $this;
	}


	public function setRendererOnCondition(
		callable $renderer,
		callable $conditionCallback
	): void
	{
		return $this->setRenderer($renderer, $conditionCallback);
	}


	public function getRenderer(): ?Renderer
	{
		return $this->renderer;
	}


	public function setReplacement(array $replacements): Colummn
	{
		$this->replacements = $replacements;

		return $this;
	}


	public function hasReplacements(): bool
	{
		return $this->replacements !== [];
	}


	public function applyReplacements(Row $row): array
	{
		$value = $row->getValue($this->column);

		if ((is_scalar($value) || $value === null) && isset($this->replacements[$value])) {
			return [true, $this->replacements[$value]];
		}

		return [false, null];
	}
}
