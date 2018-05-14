<?php declare(strict_types = 1);

namespace Ublaboo\DataGrid\Filter;

use Nette;

class FilterText extends Filter
{

	/**
	 * @var string
	 */
	protected $template = 'datagrid_filter_text.latte';

	/**
	 * @var string
	 */
	protected $type = 'text';

	/**
	 * @var bool
	 */
	protected $exact = false;

	/**
	 * @var bool
	 */
	protected $split_words_search = true;

	/**
	 * Adds text field to filter form
	 */
	public function addToFormContainer(Nette\Forms\Container $container): void
	{
		$container->addText($this->key, $this->name);

		$this->addAttributes($container[$this->key]);

		if ($this->getPlaceholder()) {
			$container[$this->key]->setAttribute('placeholder', $this->getPlaceholder());
		}
	}


	/**
	 * Return array of conditions to put in result [column1 => value, column2 => value]
	 * 	If more than one column exists in fitler text,
	 * 	than there is OR clause put betweeen their conditions
	 * Or callback in case of custom condition callback
	 *
	 * @return array|callable
	 */
	public function getCondition(): array
	{
		return array_fill_keys($this->column, $this->getValue());
	}


	public function isExactSearch(): bool
	{
		return $this->exact;
	}


	public function setExactSearch(bool $exact = true): FilterText
	{
		$this->exact = $exact;
		return $this;
	}


	public function setSplitWordsSearch(bool $split_words_search): FilterText
	{
		$this->split_words_search = (bool) $split_words_search;

		return $this;
	}


	public function hasSplitWordsSearch(): bool
	{
		return $this->split_words_search;
	}

}
