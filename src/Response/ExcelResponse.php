<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Response;

use Nette\Application\Response;
use Nette\Http\IRequest as HttpRequest;
use Nette\Http\IResponse as HttpResponse;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tracy\Debugger;

/**
 * CSV file download response
 */
class ExcelResponse implements Response
{

	public const string CONTENT_TYPE = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

	/** @var array<int|string, array<scalar>> */
	protected array $data;

	protected string $name;

	/** @var string[] */
	protected array $headers = [
		'Expires' => '0',
		'Cache-Control' => 'no-cache',
		'Pragma' => 'Public',
	];

	/**
	 * @param array<int|string, array<scalar>> $data Input data
	 */
	public function __construct(
		array $data,
		string $name = 'export.xlsx',
	)
	{
		if (!str_contains($name, '.xlsx')) {
			$name = sprintf('%s.xlsx', $name);
		}

		$this->name = $name;
		$this->data = $data;
	}

	public function send(HttpRequest $httpRequest, HttpResponse $httpResponse): void
	{
		// Disable tracy bar
		if (class_exists(Debugger::class)) {
			Debugger::$productionMode = true;
		}

		// Set Content-Type header
		$httpResponse->setContentType(self::CONTENT_TYPE);

		// Set Content-Disposition header
		$httpResponse->setHeader('Content-Disposition', sprintf('attachment; filename="%s"', $this->name));

		// Set other headers
		foreach ($this->headers as $key => $value) {
			$httpResponse->setHeader($key, $value);
		}

		if (function_exists('ob_start')) {
			ob_start();
		}

		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->createSheet(0);

		foreach ($this->data as $_rowIndex => $_row) {
			foreach ($_row as $_columnIndex => $_value) {
				$sheet->setCellValue([$_columnIndex + 1, $_rowIndex + 1], $_value);
			}
		}

		$highestColumnIndex = !empty($this->data) ? count($this->data[0]) : 0;

		for ($col = 1; $col <= $highestColumnIndex; $col++) {
			$columnLetter = Coordinate::stringFromColumnIndex($col);
			$sheet->getColumnDimension($columnLetter)->setAutoSize(true);
		}

		$spreadsheet->setActiveSheetIndex(0);

		$writer = new Xlsx($spreadsheet);
		$writer->save('php://output');

		if (function_exists('ob_end_flush')) {
			ob_end_flush();
		}
	}

}
