<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid;

use Nette;

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

	/** @var Nette\Localization\ITranslator */
	protected $translator;


	/**
	 * @param array $data
	 * @param array $columns
	 */
	public function __construct(array $data, array $columns, Nette\Localization\ITranslator $translator)
	{
		$this->data = $data;
		$this->columns = $columns;
		$this->translator = $translator;
	}


	/**
	 * Get data with header and "body"
	 * @return array
	 */
	public function getSimpleData($include_header = true)
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
			$header[] = $this->translator->translate($column->getName());
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
