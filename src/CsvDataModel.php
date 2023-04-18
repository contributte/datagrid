<?php declare(strict_types = 1);

namespace Contributte\Datagrid;

use Contributte\Datagrid\Column\Column;
use Nette\Localization\Translator;

class CsvDataModel
{

	/**
	 * @param Column[] $columns
	 */
	public function __construct(protected array $data, protected array $columns, protected Translator $translator)
	{
	}

	/**
	 * Get data with header and "body"
	 */
	public function getSimpleData(bool $includeHeader = true): array
	{
		$return = [];

		if ($includeHeader) {
			$return[] = $this->getHeader();
		}

		foreach ($this->data as $item) {
			$return[] = $this->getRow($item);
		}

		return $return;
	}

	public function getHeader(): array
	{
		$header = [];

		foreach ($this->columns as $column) {
			$header[] = $this->translator->translate($column->getName());
		}

		return $header;
	}

	/**
	 * Get item values saved into row
	 */
	public function getRow(mixed $item): array
	{
		$row = [];

		foreach ($this->columns as $column) {
			$row[] = strip_tags((string) $column->render($item));
		}

		return $row;
	}

}
