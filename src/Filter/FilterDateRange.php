<?php declare(strict_types = 1);

namespace Ublaboo\DataGrid\Filter;

use Nette;

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
	public function addToFormContainer(Nette\Forms\Container $container): void
	{
		$container = $container->addContainer($this->key);

		$container->addText('from', $this->name)
			->setAttribute('data-provide', 'datepicker')
			->setAttribute('data-date-orientation', 'bottom')
			->setAttribute('data-date-format', $this->getJsFormat())
			->setAttribute('data-date-today-highlight', 'true')
			->setAttribute('data-date-autoclose', 'true');

		$container->addText('to', $this->name_second)
			->setAttribute('data-provide', 'datepicker')
			->setAttribute('data-date-orientation', 'bottom')
			->setAttribute('data-date-format', $this->getJsFormat())
			->setAttribute('data-date-today-highlight', 'true')
			->setAttribute('data-date-autoclose', 'true');

		$this->addAttributes($container['from']);
		$this->addAttributes($container['to']);

		if ($this->grid->hasAutoSubmit()) {
			$container['from']->setAttribute('data-autosubmit-change', true);
			$container['to']->setAttribute('data-autosubmit-change', true);
		}

		if ($placeholder_array = $this->getPlaceholder()) {
			$text_from = reset($placeholder_array);

			if ($text_from) {
				$container['from']->setAttribute('placeholder', $text_from);
			}

			$text_to = end($placeholder_array);

			if ($text_to && ($text_to !== $text_from)) {
				$container['to']->setAttribute('placeholder', $text_to);
			}
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
