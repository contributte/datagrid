<?php

/**
 * * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
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


	public function __construct(array $data, array $columns)
	{
		$this->data = $data;
		$this->columns = $columns;
	}


	/**
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


	public function getHeader()
	{
		$header = [];

		foreach ($this->columns as $column) {
			$header[] = $column->getName();
		}

		return $header;
	}


	public function getRow($item)
	{
		$row = [];

		foreach ($this->columns as $column) {
			$row[] = strip_tags($column->render($item));
		}

		return $row;
	}

}
