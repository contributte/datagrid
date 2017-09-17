<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid;

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
	 * @var array
	 */
	protected $summary;

	/**
	 * @var array
	 */
	protected $format = [];

	/**
	 * @var NULL|callable
	 */
	protected $rowCallback;

	/**
	 * @var Renderer|NULL
	 */
	protected $renderer;


	/**
	 * @param DataGrid $datagrid
	 * @param array    $columns
	 */
	public function __construct(DataGrid $datagrid, array $columns, $rowCallback)
	{
		$this->summary = array_fill_keys(array_values($columns), 0);
		$this->datagrid = $datagrid;
		$this->rowCallback = $rowCallback;

		foreach ($this->summary as $key => $sum) {
			$column = $this->datagrid->getColumn($key);

			if ($column instanceof ColumnNumber) {
				$arg = $column->getFormat();
				array_unshift($arg, $key);

				call_user_func_array([$this, 'setFormat'], $arg);
			}
		}
	}


	/**
	 * Get value from column using Row::getValue() or custom callback
	 * @param Row    	    $row
	 * @param Column\Column $column
	 * @return bool
	 */
	private function getValue(Row $row, $column)
	{
		if (!$this->rowCallback) {
			return $row->getValue($column->getColumn());
		}

		return call_user_func_array($this->rowCallback, [$row->getItem(), $column->getColumn()]);
	}


	/**
	 * @param Row $row
	 */
	public function add(Row $row)
	{
		foreach ($this->summary as $key => $sum) {
			$column = $this->datagrid->getColumn($key);

			$value = $this->getValue($row, $column);
			$this->summary[$key] += $value;
		}
	}


	/**
	 * @param  string $key
	 * @return mixed
	 */
	public function render($key)
	{
		/**
		 * Renderer function may be used
		 */
		try {
			return $this->useRenderer($key);
		} catch (DataGridColumnRendererException $e) {
			/**
			 * Do not use renderer
			 */
		}

		if (!isset($this->summary[$key])) {
			return null;
		}

		return number_format(
			$this->summary[$key],
			$this->format[$key][0],
			$this->format[$key][1],
			$this->format[$key][2]
		);
	}


	/**
	 * Try to render summary with custom renderer
	 * @param  string $key
	 * @return mixed
	 */
	public function useRenderer($key)
	{
		if (!isset($this->summary[$key])) {
			return null;
		}

		$renderer = $this->getRenderer();

		if (!$renderer) {
			throw new DataGridColumnRendererException;
		}

		return call_user_func_array($renderer->getCallback(), [$this->summary[$key], $key]);
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
	 * Set renderer callback
	 * @param callable $renderer
	 */
	public function setRenderer(callable $renderer)
	{
		$this->renderer = new Renderer($renderer, NULL);

		return $this;
	}


	/**
	 * Set number format
	 * @param string $key
	 * @param int    $decimals
	 * @param string $dec_point
	 * @param string $thousands_sep
	 */
	public function setFormat($key, $decimals = 0, $dec_point = '.', $thousands_sep = ' ')
	{
		$this->format[$key] = [$decimals, $dec_point, $thousands_sep];

		return $this;
	}


	/**
	 * @param  array  $columns
	 * @return bool
	 */
	public function someColumnsExist(array $columns)
	{
		foreach ($columns as $key => $column) {
			if (isset($this->summary[$key])) {
				return true;
			}
		}

		return false;
	}
}
