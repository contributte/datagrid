<?php declare(strict_types=1);

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\Export;

use Contributte\Application\Response\CSVResponse;
use Nette;
use Nette\Application\UI\Presenter;
use Ublaboo\DataGrid\CsvDataModel;
use Ublaboo\DataGrid\DataGrid;

class ExportCsv extends Export
{

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
	protected $outputEncoding;

	/**
	 * @var boolean
	 */
	protected $includeBom;

	/**
	 * @var string
	 */
	protected $delimiter;


	public function __construct(
		DataGrid $grid,
		string $text,
		string $name,
		bool $filtered,
		?string $outputEncoding = 'utf-8',
		?string $delimiter = ';',
		?bool $includeBom = false
	) {
		$this->grid = $grid;
		$this->text = $text;
		$this->filtered = (bool) $filtered;

		if (strpos($name, '.csv') === false) {
			$name .= '.csv';
		}

		$this->name =  $name;
		$this->outputEncoding = $outputEncoding;
		$this->delimiter = $delimiter;
		$this->includeBom = $includeBom;
	}


	/**
	 * Call export callback
	 */
	public function invoke(array $data): void
	{
		$columns = $this->getColumns() ?: $this->grid->getColumns();

		$csv_data_model = new CsvDataModel($data, $columns, $this->grid->getTranslator());

		if ($this->grid->getPresenter() instanceof Presenter) {
			$this->grid->getPresenter()->sendResponse(new CSVResponse(
				$csv_data_model->getSimpleData(),
				$this->name,
				$this->outputEncoding,
				$this->delimiter,
				$this->includeBom
			));

			exit(0);
		}
	}
}
