<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\Export;

use Ublaboo\DataGrid\CsvDataModel;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\Responses\CSVResponse;
use Nette;

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
	 * @var string
	 */
	protected $output_encoding = 'utf-8';

	/**
	 * @var string
	 */
	protected $delimiter = ';';


	/**
	 * @param string      $text
	 * @param string      $csv_file_name
	 * @param bool        $filtered
	 * @param string|null $output_encoding
	 * @param string|null $delimiter
	 */
	public function __construct(
		$text,
		$csv_file_name,
		$filtered,
		$output_encoding = NULL,
		$delimiter = NULL
	) {
		$this->text = $text;
		$this->filtered = (bool) $filtered;
		$this->name = strpos($csv_file_name, '.csv') !== FALSE ? $csv_file_name : "$csv_file_name.csv";

		if ($output_encoding) {
			$this->output_encoding = $output_encoding;
		}

		if ($delimiter) {
			$this->delimiter = $delimiter;
		}
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

		$csv_data_model = new CsvDataModel($data, $columns, $grid->getTranslator());

		if ($grid->getPresenter() instanceof Nette\Application\UI\Presenter) {
			$grid->getPresenter()->sendResponse(new CSVResponse(
				$csv_data_model->getSimpleData(),
				$this->name,
				$this->output_encoding,
				$this->delimiter
			));

			exit(0);
		}
	}

}
