<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Export;

use Contributte\DataGrid\DataGrid;
use Contributte\Datagrid\ExcelDataModel;
use Contributte\Datagrid\Response\ExcelResponse;

class ExportExcel extends Export
{

	public function __construct(
		DataGrid $grid,
		string $text,
		string $name,
		bool $filtered,
	)
	{
		if (!str_contains($name, '.xlsx')) {
			$name .= '.xlsx';
		}

		parent::__construct(
			$grid,
			$text,
			$this->getExportCallback($name),
			$filtered
		);
	}

	private function getExportCallback(string $name): callable
	{
		return function (
			array $data,
			DataGrid $grid
		) use ($name): void {
			$columns = $this->getColumns();

			if ($columns === []) {
				$columns = $this->grid->getColumns();
			}

			$excelDataModel = new ExcelDataModel($data, $columns, $this->grid->getTranslator());

			$this->grid->getPresenter()->sendResponse(new ExcelResponse($excelDataModel->getSimpleData(), $name));
		};
	}

}
