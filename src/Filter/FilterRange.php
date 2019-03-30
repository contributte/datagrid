<?php declare(strict_types=1);

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\Filter;

use Nette;
use Nette\Forms\Container;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Exception\DataGridFilterRangeException;

class FilterRange extends OneColumnFilter
{

	/**
	 * @var array
	 */
	protected $placeholders = [];

	/**
	 * @var string
	 */
	protected $nameSecond;

	/**
	 * @var string
	 */
	protected $template = 'datagrid_filter_range.latte';

	/**
	 * @var string
	 */
	protected $type = 'range';


	/**
	 * @param DataGrid $grid
	 * @param string   $key
	 * @param string   $name
	 * @param string   $column
	 * @param string   $nameSecond
	 */
	public function __construct(
		DataGrid $grid,
		string $key,
		string $name,
		string $column,
		string $nameSecond
	) {
		parent::__construct($grid, $key, $name, $column);

		$this->nameSecond = $nameSecond;
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
				$from->setAttribute('placeholder', $text_from);
			}

			$text_to = end($placeholders);

			if ($text_to && ($text_to != $text_from)) {
				$to->setAttribute('placeholder', $text_to);
			}
		}
	}


	/**
	 * Set html attr placeholder of both inputs
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
				'from' => isset($value['from']) ? $value['from'] : '',
				'to' => isset($value['to']) ? $value['to'] : '',
			]
		];
	}
}
