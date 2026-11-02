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

	protected bool $conjunctionSearch = false;

	protected bool $quotedPhraseSearch = false;

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

	/**
	 * @return static
	 */
	public function setQuotedPhraseSearch(bool $enabled = true): self
	{
		$this->quotedPhraseSearch = $enabled;

		return $this;
	}

	/**
	 * Extract substring terms without changing the raw filter value.
	 */
	public function getSearchTerms(mixed $value): array
	{
		if (!$this->splitWordsSearch || $this->exact) {
			return [$value];
		}

		if (!$this->quotedPhraseSearch) {
			return explode(' ', $value);
		}

		$terms = [];
		$term = '';
		$quoted = false;
		$length = strlen($value);

		for ($i = 0; $i < $length; $i++) {
			$character = $value[$i];
			if ($character === '\\' && $i + 1 < $length && ($value[$i + 1] === '"' || $value[$i + 1] === '\\')) {
				$term .= $value[++$i];
			} elseif ($character === '"') {
				$quoted = !$quoted;
			} elseif (!$quoted && str_contains(" \t\r\n\v\f", $character)) {
				if ($term !== '') {
					$terms[] = $term;
					$term = '';
				}
			} else {
				$term .= $character;
			}
		}

		if ($term !== '') {
			$terms[] = $term;
		}

		return $terms === [] ? [''] : $terms;
	}

	public function setConjunctionSearch(bool $conjunctionSearch = true): void
	{
		$this->conjunctionSearch = $conjunctionSearch;
	}

	public function hasConjunctionSearch(): bool
	{
		return $this->conjunctionSearch;
	}

}
