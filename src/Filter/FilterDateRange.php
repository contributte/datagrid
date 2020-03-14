<?php

declare(strict_types=1);

namespace Ublaboo\DataGrid\Filter;

use Nette\Forms\Container;

class FilterDateRange extends FilterRange implements IFilterDate
{

	/**
	 * @var string
	 */
	protected $template = 'datagrid_filter_daterange.latte';

	/**
	 * @var array
	 */
	protected $format = ['j. n. Y', 'd. m. yyyy'];

	/**
	 * @var string
	 */
	protected $type = 'date-range';

	/**
	 * Adds select box to filter form
	 */
	public function addToFormContainer(Container $container): void
	{
		$container = $container->addContainer($this->key);

		$from = $container->addText('from', $this->name);

		$from->setHtmlAttribute('data-provide', 'datepicker')
			->setHtmlAttribute('data-date-orientation', 'bottom')
			->setHtmlAttribute('data-date-format', $this->getJsFormat())
			->setHtmlAttribute('data-date-today-highlight', 'true')
			->setHtmlAttribute('data-date-autoclose', 'true');

		$to = $container->addText('to', $this->nameSecond);

		$to->setHtmlAttribute('data-provide', 'datepicker')
			->setHtmlAttribute('data-date-orientation', 'bottom')
			->setHtmlAttribute('data-date-format', $this->getJsFormat())
			->setHtmlAttribute('data-date-today-highlight', 'true')
			->setHtmlAttribute('data-date-autoclose', 'true');

		$this->addAttributes($from);
		$this->addAttributes($to);

		if ($this->grid->hasAutoSubmit()) {
			$from->setHtmlAttribute('data-autosubmit-change', true);
			$to->setHtmlAttribute('data-autosubmit-change', true);
		}

		$placeholders = $this->getPlaceholders();

		if ($placeholders !== []) {
			$textFrom = reset($placeholders);

			if ($textFrom) {
				$from->setHtmlAttribute('placeholder', $textFrom);
			}

			$textTo = end($placeholders);

			if ($textTo && ($textTo !== $textFrom)) {
				$to->setHtmlAttribute('placeholder', $textTo);
			}
		}
	}


	/**
	 * Set format for datepicker etc
	 */
	public function setFormat(string $phpFormat, string $jsFormat): IFilterDate
	{
		$this->format = [$phpFormat, $jsFormat];

		return $this;
	}


	/**
	 * Get php format for datapicker
	 */
	public function getPhpFormat(): string
	{
		return $this->format[0];
	}


	/**
	 * Get js format for datepicker
	 */
	public function getJsFormat(): string
	{
		return $this->format[1];
	}

}
