<?php

declare(strict_types=1);

namespace Ublaboo\DataGrid;

use Ublaboo\DataGrid\Column\Column;
use Ublaboo\DataGrid\Column\ColumnNumber;
use Ublaboo\DataGrid\Column\Renderer;
use Ublaboo\DataGrid\Exception\DataGridColumnRendererException;

class ColumnsSummary
{

	/**
	 * @var DataGrid
	 */
	protected $datagrid;

	/**
	 * @var array|int[]
	 */
	protected $summary;

	/**
	 * @var array
	 */
	protected $format = [];

	/**
	 * @var callable|null
	 */
	protected $rowCallback = null;

	/**
	 * @var Renderer|null
	 */
	protected $renderer;

	/**
	 * @var bool
	 */
	protected $positionTop = false;


	/**
	 * @param array|string[] $columns
	 */
	public function __construct(
		DataGrid $datagrid,
		array $columns,
		?callable $rowCallback
	) {
		$this->summary = array_fill_keys(array_values($columns), 0);
		$this->datagrid = $datagrid;
		$this->rowCallback = $rowCallback;

		foreach (array_keys($this->summary) as $key) {
			$column = $this->datagrid->getColumn($key);

			if ($column instanceof ColumnNumber) {
				$arg = $column->getFormat();
				array_unshift($arg, $key);

				$this->setFormat(...$arg);
			}
		}
	}


	public function add(Row $row): void
	{
		foreach (array_keys($this->summary) as $key) {
			$column = $this->datagrid->getColumn($key);

			$value = $this->getValue($row, $column);
			$this->summary[$key] += $value;
		}
	}


	public function render(string $key): ?string
	{
		try {
			return $this->useRenderer($key);
		} catch (DataGridColumnRendererException $e) {
		}

		if (!isset($this->summary[$key])) {
			return '';
		}

		return number_format(
			$this->summary[$key],
			$this->format[$key][0],
			$this->format[$key][1],
			$this->format[$key][2]
		);
	}


	/**
	 * @return mixed
	 */
	public function useRenderer(string $key)
	{
		if (!isset($this->summary[$key])) {
			return null;
		}

		$renderer = $this->getRenderer();

		if ($renderer === null) {
			throw new DataGridColumnRendererException;
		}

		return call_user_func_array($renderer->getCallback(), [$this->summary[$key], $key]);
	}


	public function getRenderer(): ?Renderer
	{
		return $this->renderer;
	}


	/**
	 * @return static
	 */
	public function setRenderer(callable $renderer): self
	{
		$this->renderer = new Renderer($renderer, null);

		return $this;
	}


	/**
	 * @return static
	 */
	public function setFormat(
		string $key,
		int $decimals = 0,
		string $dec_point = '.',
		string $thousands_sep = ' '
	): self
	{
		$this->format[$key] = [$decimals, $dec_point, $thousands_sep];

		return $this;
	}


	public function someColumnsExist(array $columns): bool
	{
		foreach (array_keys($columns) as $key) {
			if (isset($this->summary[$key])) {
				return true;
			}
		}

		return false;
	}


	/**
	 * @return static
	 */
	public function setPositionTop(bool $top = true): self
	{
		$this->positionTop = $top !== false;

		return $this;
	}


	public function getPositionTop(): bool
	{
		return $this->positionTop;
	}


	/**
	 * Get value from column using Row::getValue() or custom callback
	 *
	 * @return mixed
	 */
	private function getValue(Row $row, Column $column)
	{
		if ($this->rowCallback === null) {
			return $row->getValue($column->getColumn());
		}

		return call_user_func_array($this->rowCallback, [$row->getItem(), $column->getColumn()]);
	}
}
