<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Export;

use Contributte\Application\Response\CSVResponse;
use Contributte\Datagrid\CsvDataModel;
use Contributte\Datagrid\Datagrid;

class ExportCsv extends Export
{

	public function __construct(
		Datagrid $grid,
		string $text,
		string $name,
		bool $filtered,
		string $outputEncoding = 'utf-8',
		string $delimiter = ';',
		bool $includeBom = false
	)
	{
		if (!str_contains($name, '.csv')) {
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
			Datagrid $grid
		) use (
			$name,
			$outputEncoding,
			$delimiter,
			$includeBom
): void {
			$columns = $this->getColumns();

			if ($columns === []) {
				$columns = $this->grid->getColumns();
			}

			$csvDataModel = new CsvDataModel($data, $columns, $this->grid->getTranslator());

			$this->grid->getPresenter()->sendResponse(new CSVResponse(
				$csvDataModel->getSimpleData(),
				$name,
				$outputEncoding,
				$delimiter,
				$includeBom
			));
		};
	}

}
