<?php declare(strict_types = 1);

namespace Ublaboo\DataGrid\Filter;

use Nette;

class FilterDate extends Filter implements IFilterDate
{

	/**
	 * @var string
	 */
	protected $template = 'datagrid_filter_date.latte';

	/**
	 * @var array
	 */
	protected $format = ['j. n. Y', 'd. m. yyyy'];

	/**
	 * @var string
	 */
	protected $type = 'date';

	/**
	 * Adds select box to filter form
	 */
	public function addToFormContainer(Nette\Forms\Container $container): void
	{
		$container->addText($this->key, $this->name)
			->setAttribute('data-provide', 'datepicker')
			->setAttribute('data-date-orientation', 'bottom')
			->setAttribute('data-date-format', $this->getJsFormat())
			->setAttribute('data-date-today-highlight', 'true')
			->setAttribute('data-date-autoclose', 'true');

		$this->addAttributes($container[$this->key]);

		if ($this->grid->hasAutoSubmit()) {
			$container[$this->key]->setAttribute('data-autosubmit-change', true);
		}

		if ($this->getPlaceholder()) {
			$container[$this->key]->setAttribute('placeholder', $this->getPlaceholder());
		}
	}


	/**
	 * Set format for datepicker etc
	 *
	 * @return static
	 */
	public function setFormat(string $php_format, string $js_format)
	{
		$this->format = [$php_format, $js_format];

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
