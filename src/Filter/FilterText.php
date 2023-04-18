<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Filter;

use Contributte\Datagrid\Datagrid;
use Nette\Forms\Container;

class FilterText extends Filter
{

	protected ?string $template = 'datagrid_filter_text.latte';

	protected ?string $type = 'text';

	protected bool $exact = false;

	protected bool $splitWordsSearch = true;

	/**
	 * @param array|string[] $columns
	 */
	public function __construct(
		Datagrid $grid,
		string $key,
		string $name,
		protected array $columns
	)
	{
		parent::__construct($grid, $key, $name);
	}

	/**
	 * Adds text field to filter form
	 */
	public function addToFormContainer(Container $container): void
	{
		$control = $container->addText($this->key, $this->name);

		$this->addAttributes($control);

		if ($this->getPlaceholder() !== null) {
			$control->setHtmlAttribute('placeholder', $this->getPlaceholder());
		}
	}

	/**
	 * Return array of conditions to put in result [column1 => value, column2 => value]
	 * 	If more than one column exists in fitler text,
	 * 	than there is OR clause put betweeen their conditions
	 * Or callback in case of custom condition callback
	 */
	public function getCondition(): array
	{
		return array_fill_keys($this->columns, $this->getValue());
	}

	public function isExactSearch(): bool
	{
		return $this->exact;
	}

	/**
	 * @return static
	 */
	public function setExactSearch(bool $exact = true): self
	{
		$this->exact = $exact;

		return $this;
	}

	/**
	 * @return static
	 */
	public function setSplitWordsSearch(bool $splitWordsSearch): self
	{
		$this->splitWordsSearch = $splitWordsSearch;

		return $this;
	}

	public function hasSplitWordsSearch(): bool
	{
		return $this->splitWordsSearch;
	}

}
