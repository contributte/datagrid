<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Filter;

use Nette\Forms\Container;

class FilterDate extends OneColumnFilter implements IFilterDate
{

	protected ?string $template = 'datagrid_filter_text.latte';

	protected ?string $type = 'date';

	public function addToFormContainer(Container $container): void
	{
		$control = $container->addText($this->key, $this->name);

		$control->setHtmlType('date');

		$this->addAttributes($control);

		if ($this->grid->hasAutoSubmit()) {
			$control->setHtmlAttribute('data-autosubmit-change', true);
		}

		if ($this->getPlaceholder() !== null) {
			$control->setHtmlAttribute('placeholder', $this->getPlaceholder());
		}
	}

	/**
	 * Get php format for datapicker
	 */
	public function getPhpFormat(): string
	{
		return 'Y-m-d';
	}

}
