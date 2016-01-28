<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid;

class CsvDataModel
{

	/**
	 * @var array
	 */
	protected $data;

	/**
	 * @var Column\Column[]
	 */
	protected $columns;


	/**
	 * @param array $data
	 * @param array $columns
	 */
	public function __construct(array $data, array $columns)
	{
		$this->data = $data;
		$this->columns = $columns;
	}


	/**
	 * Get data with header and "body"
	 * @return array
	 */
	public function getSimpleData($include_header = TRUE)
	{
		$return = [];

		if ($include_header) {
			$return[] = $this->getHeader();
		}

		foreach ($this->data as $item) {
			$return[] = $this->getRow($item);
		}

		return $return;
	}


	/**
	 * Get data header
	 * @return array
	 */
	public function getHeader()
	{
		$header = [];

		foreach ($this->columns as $column) {
			$header[] = $column->getName();
		}

		return $header;
	}


	/**
	 * Get item values saved into row
	 * @param  mixed $item
	 * @return array
	 */
	public function getRow($item)
	{
		$row = [];

		foreach ($this->columns as $column) {
			$row[] = strip_tags($column->render($item));
		}

		return $row;
	}

}
