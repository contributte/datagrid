<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\Export;

use Nette;
use Ublaboo\DataGrid\CsvDataModel;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\Responses\CSVResponse;

class ExportCsv extends Export
{

	/**
	 * @var bool
	 */
	protected $is_ajax = false;

	/**
	 * @var bool
	 */
	protected $filtered;

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var string
	 */
	protected $output_encoding = 'utf-8';

	/**
	 * @var boolean
	 */
	protected $include_bom = false;

	/**
	 * @var string
	 */
	protected $delimiter = ';';


	/**
	 * @param DataGrid    $grid
	 * @param string      $text
	 * @param string      $csv_file_name
	 * @param bool        $filtered
	 * @param string|null $output_encoding
	 * @param string|null $delimiter
	 * @param bool        $include_bom
	 */
	public function __construct(
		DataGrid $grid,
		$text,
		$csv_file_name,
		$filtered,
		$output_encoding = null,
		$delimiter = null,
		$include_bom = false
	) {
		$this->grid = $grid;
		$this->text = $text;
		$this->filtered = (bool) $filtered;
		$this->name = strpos($csv_file_name, '.csv') !== false ? $csv_file_name : "$csv_file_name.csv";

		if ($output_encoding) {
			$this->output_encoding = $output_encoding;
		}

		if ($delimiter) {
			$this->delimiter = $delimiter;
		}

		if ($include_bom) {
			$this->include_bom = $include_bom;
		}
	}


	/**
	 * Call export callback
	 * @param  array    $data
	 * @return void
	 */
	public function invoke(array $data)
	{
		$columns = $this->getColumns() ?: $this->grid->getColumns();

		$csv_data_model = new CsvDataModel($data, $columns, $this->grid->getTranslator());

		if ($this->grid->getPresenter() instanceof Nette\Application\UI\Presenter) {
			$this->grid->getPresenter()->sendResponse(new CSVResponse(
				$csv_data_model->getSimpleData(),
				$this->name,
				$this->output_encoding,
				$this->delimiter,
				$this->include_bom
			));

			exit(0);
		}
	}
}
