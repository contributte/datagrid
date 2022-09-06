<?php

declare(strict_types=1);

namespace Ublaboo\DataGrid\Filter;

use Nette\Forms\Container;

class FilterDateTimeRange extends FilterRange implements IFilterDateTime
{

	/**
	 * @var string
	 */
	protected $template = 'datagrid_filter_datetimerange.latte';

	/**
	 * @var array
	 */
	protected $format = ['j. n. Y H:i', 'dd. mm. yyyy hh:ii'];

	/**
	 * @var string
	 */
	protected $type = 'datetime-range';

	/**
	 * @var string
	 */
	protected $locale = 'en';

	/**
	 * Adds select box to filter form
	 */
	public function addToFormContainer(Container $container): void
	{
		$container = $container->addContainer($this->key);

		$from = $container->addText('from', $this->name);

		$from->setAttribute('data-provide', 'datetimepicker')
			->setAttribute('data-date-orientation', 'bottom')
			->setAttribute('data-date-format', $this->getJsFormat())
			->setAttribute('data-date-language', $this->getLocale())
			->setAttribute('data-date-today-highlight', 'true')
			->setAttribute('data-date-autoclose', 'true');

		$to = $container->addText('to', $this->nameSecond);

		$to->setAttribute('data-provide', 'datetimepicker')
			->setAttribute('data-date-orientation', 'bottom')
			->setAttribute('data-date-format', $this->getJsFormat())
			->setAttribute('data-date-language', $this->getLocale())
			->setAttribute('data-date-today-highlight', 'true')
			->setAttribute('data-date-autoclose', 'true');

		$this->addAttributes($from);
		$this->addAttributes($to);

		if ($this->grid->hasAutoSubmit()) {
			$from->setAttribute('data-autosubmit-change', true);
			$to->setAttribute('data-autosubmit-change', true);
		}

		$placeholders = $this->getPlaceholders();

		if ($placeholders !== []) {
			$textFrom = reset($placeholders);

			if ($textFrom) {
				$from->setAttribute('placeholder', $textFrom);
			}

			$textTo = end($placeholders);

			if ($textTo && ($textTo !== $textFrom)) {
				$to->setAttribute('placeholder', $textTo);
			}
		}
	}


	/**
	 * Set format for datepicker etc
	 */
	public function setFormat(string $phpFormat, string $jsFormat): IFilterDateTime
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

	/**
	 * Get locale for picker
	 */
	public function getLocale(): string
	{
		return $this->locale;
	}

	/**
	 * Set locale for picker
	 */
	public function setLocale(string $locale)
	{
		return $this->locale = $locale;
	}

}
