<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Filter;

use Nette\Forms\Container;

class FilterDate extends OneColumnFilter implements IFilterDate
{

	protected ?string $template = 'datagrid_filter_date.latte';

	protected array $format = ['j. n. Y', 'd. m. yyyy'];

	protected ?string $type = 'date';

	public function addToFormContainer(Container $container): void
	{
		$control = $container->addText($this->key, $this->name);

		$control->setHtmlAttribute('data-provide', 'datepicker')
			->setHtmlAttribute('data-date-orientation', 'bottom')
			->setHtmlAttribute('data-date-format', $this->getJsFormat())
			->setHtmlAttribute('data-date-today-highlight', 'true')
			->setHtmlAttribute('data-date-autoclose', 'true');

		$this->addAttributes($control);

		if ($this->grid->hasAutoSubmit()) {
			$control->setHtmlAttribute('data-autosubmit-change', true);
		}

		if ($this->getPlaceholder() !== null) {
			$control->setHtmlAttribute('placeholder', $this->getPlaceholder());
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
