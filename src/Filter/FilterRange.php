<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Filter;

use Contributte\Datagrid\Datagrid;
use Nette\Forms\Container;

class FilterRange extends OneColumnFilter
{

	protected array $placeholders = [];

	protected ?string $template = 'datagrid_filter_range.latte';

	protected ?string $type = 'range';

	public function __construct(
		Datagrid $grid,
		string $key,
		string $name,
		string $column,
		protected string $nameSecond
	)
	{
		parent::__construct($grid, $key, $name, $column);
	}

	public function addToFormContainer(Container $container): void
	{
		$container = $container->addContainer($this->key);

		$from = $container->addText('from', $this->name);
		$to = $container->addText('to', $this->nameSecond);

		$this->addAttributes($from);
		$this->addAttributes($to);

		$placeholders = $this->getPlaceholders();

		if ($placeholders !== []) {
			$text_from = reset($placeholders);

			if ($text_from) {
				$from->setHtmlAttribute('placeholder', $text_from);
			}

			$text_to = end($placeholders);

			if ($text_to && ($text_to !== $text_from)) {
				$to->setHtmlAttribute('placeholder', $text_to);
			}
		}
	}

	/**
	 * Set html attr placeholder of both inputs
	 *
	 * @return static
	 */
	public function setPlaceholders(array $placeholders): self
	{
		$this->placeholders = $placeholders;

		return $this;
	}

	/**
	 * Get html attr placeholders
	 */
	public function getPlaceholders(): array
	{
		return $this->placeholders;
	}

	/**
	 * Get filter condition
	 */
	public function getCondition(): array
	{
		$value = $this->getValue();

		return [
			$this->column => [
				'from' => $value['from'] ?? '',
				'to' => $value['to'] ?? '',
			],
		];
	}

}
