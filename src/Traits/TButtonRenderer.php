<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Traits;

use Contributte\Datagrid\Column\Renderer;
use Contributte\Datagrid\Exception\DatagridColumnRendererException;
use Contributte\Datagrid\Exception\DatagridException;
use Contributte\Datagrid\Row;

trait TButtonRenderer
{

	protected ?Renderer $renderer = null;

	protected array $replacements = [];

	/**
	 * @throws DatagridColumnRendererException
	 */
	public function useRenderer(?Row $row = null): mixed
	{
		$renderer = $this->getRenderer();

		$args = $row instanceof Row ? [$row->getItem()] : [];

		if ($renderer === null) {
			throw new DatagridColumnRendererException();
		}

		if ($renderer->getConditionCallback() !== null) {
			if (call_user_func_array($renderer->getConditionCallback(), $args) === false) {
				throw new DatagridColumnRendererException();
			}

			return call_user_func_array($renderer->getCallback(), $args);
		}

		return call_user_func_array($renderer->getCallback(), $args);
	}

	/**
	 * Set renderer callback and (it may be optional - the condition callback will decide)
	 *
	 * @return static
	 * @throws DatagridException
	 */
	public function setRenderer(
		callable $renderer,
		?callable $conditionCallback = null
	): self
	{
		if ($this->hasReplacements()) {
			throw new DatagridException('Use either Column::setReplacement() or Column::setRenderer, not both.');
		}

		$this->renderer = new Renderer($renderer, $conditionCallback);

		return $this;
	}

	/**
	 * @return static
	 */
	public function setRendererOnCondition(
		callable $renderer,
		callable $conditionCallback
	): self
	{
		return $this->setRenderer($renderer, $conditionCallback);
	}

	public function getRenderer(): ?Renderer
	{
		return $this->renderer;
	}

	public function hasReplacements(): bool
	{
		return $this->replacements !== [];
	}

	public function applyReplacements(Row $row, string $column): array
	{
		$value = $row->getValue($column);

		if ((is_scalar($value) || $value === null) &&
			isset($this->replacements[gettype($value) === 'double' ? (int) $value : $value])) {
			return [true, $this->replacements[gettype($value) === 'double' ? (int) $value : $value]];
		}

		return [false, null];
	}

}
