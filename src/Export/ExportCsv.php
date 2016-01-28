<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\Export;

use Ublaboo\DataGrid\CsvDataModel,
	Ublaboo\DataGrid\DataGrid,
	Ublaboo\Responses\CSVResponse;

class ExportCsv extends Export
{

	/**
	 * @var bool
	 */
	protected $is_ajax = FALSE;

	/**
	 * @var bool
	 */
	protected $filtered;

	/**
	 * @var string
	 */
	protected $name;


	/**
	 * @param string $text
	 * @param string $csv_file_name
	 * @param bool   $filtered
	 */
	public function __construct($text, $csv_file_name, $filtered)
	{
		$this->text = $text;
		$this->filtered = (bool) $filtered;
		$this->name = strpos($csv_file_name, '.csv') !== FALSE ? $csv_file_name : "$csv_file_name.csv";
	}


	/**
	 * Call export callback
	 * @param  array    $data
	 * @param  DataGrid $grid
	 * @return void
	 */
	public function invoke(array $data, DataGrid $grid)
	{
		$columns = $this->getColumns() ?: $grid->getColumns();

		$csv_data_model = new CsvDataModel($data, $columns);

		$grid->getPresenter()->sendResponse(new CSVResponse(
			$csv_data_model->getSimpleData(),
			$this->name
		));

		exit(0);
	}

}
