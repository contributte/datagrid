<?php

declare(strict_types=1);

namespace Ublaboo\DataGrid\Filter;

use Nette\Forms\Container;

class FilterDateTime extends OneColumnFilter implements IFilterDateTime
{

	/**
	 * @var string
	 */
	protected $template = 'datagrid_filter_datetime.latte';

	/**
	 * @var array
	 */
	protected $format = ['j. n. Y H:i', 'dd. mm. yyyy hh:ii'];
	
	/**
	 * @var string
	 */
	protected $type = 'datetime';

	/**
	 * @var string
	 */
	protected $locale = 'en';

	public function addToFormContainer(Container $container): void
	{
		$control = $container->addText($this->key, $this->name);

		$control->setAttribute('data-provide', 'datetimepicker')
			->setAttribute('data-date-orientation', 'bottom')
			->setAttribute('data-date-format', $this->getJsFormat())
			->setAttribute('data-date-language', $this->getLocale())
			->setAttribute('data-date-today-highlight', 'true')
			->setAttribute('data-date-autoclose', 'true');			

		$this->addAttributes($control);

		if ($this->grid->hasAutoSubmit()) {
			$control->setAttribute('data-autosubmit-change', true);
		}

		if ($this->getPlaceholder() !== null) {
			$control->setAttribute('placeholder', $this->getPlaceholder());
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
