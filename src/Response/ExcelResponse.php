<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Response;

use Nette\Application\Response;
use Nette\Http\IRequest as HttpRequest;
use Nette\Http\IResponse as HttpResponse;
use Tracy\Debugger;
use XLSXWriter;

/**
 * CSV file download response
 */
class ExcelResponse implements Response
{

	public const CONTENT_TYPE = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

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

		$writer = new XLSXWriter();
		$writer->writeSheet($this->data);
		$writer->writeToStdOut();

		if (function_exists('ob_end_flush')) {
			ob_end_flush();
		}
	}

}
