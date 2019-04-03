<?php

declare(strict_types=1);

namespace Ublaboo\DataGrid\Export;

use Contributte\Application\Response\CSVResponse;
use Nette\Application\UI\Presenter;
use Ublaboo\DataGrid\CsvDataModel;
use Ublaboo\DataGrid\DataGrid;

class ExportCsv extends Export
{

	public function __construct(
		DataGrid $grid,
		string $text,
		string $name,
		bool $filtered,
		string $outputEncoding = 'utf-8',
		string $delimiter = ';',
		bool $includeBom = false
	)
	{
		if (strpos($name, '.csv') === false) {
			$name .= '.csv';
		}

		parent::__construct(
			$grid,
			$text,
			$this->getExportCallback($name, $outputEncoding, $delimiter, $includeBom),
			$filtered
		);
	}


	private function getExportCallback(
		string $name,
		string $outputEncoding,
		string $delimiter,
		bool $includeBom
	): callable
	{
		return function (
			array $data,
			DataGrid $grid
		) use ($name, $outputEncoding, $delimiter, $includeBom): void {
			$columns = $this->getColumns() ?: $this->grid->getColumns();

			$csvDataModel = new CsvDataModel($data, $columns, $this->grid->getTranslator());

			if ($this->grid->getPresenter() instanceof Presenter) {
				$this->grid->getPresenter()->sendResponse(new CSVResponse(
					$csvDataModel->getSimpleData(),
					$name,
					$outputEncoding,
					$delimiter,
					$includeBom
				));

				exit(0);
			}
		};
	}
}
