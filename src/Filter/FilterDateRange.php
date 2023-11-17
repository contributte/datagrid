<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Filter;

use Nette\Forms\Container;

class FilterDateRange extends FilterRange implements IFilterDate
{

	protected ?string $template = 'datagrid_filter_range.latte';

	protected ?string $type = 'date-range';

	/**
	 * Adds select box to filter form
	 */
	public function addToFormContainer(Container $container): void
	{
		$container = $container->addContainer($this->key);

		$from = $container->addText('from', $this->name);

		$from->setHtmlType('date');

		$to = $container->addText('to', $this->nameSecond);

		$to->setHtmlType('date');

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
	 * Get php format for datapicker
	 */
	public function getPhpFormat(): string
	{
		return 'Y-m-d';
	}

}
