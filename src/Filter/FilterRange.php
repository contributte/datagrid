<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\Filter;

use Nette;
use Ublaboo\DataGrid\Exception\DataGridFilterRangeException;

class FilterRange extends Filter
{

	/**
	 * @var array
	 */
	protected $placeholder_array;

	/**
	 * @var string
	 */
	protected $name_second;

	/**
	 * @var string
	 */
	protected $template = 'datagrid_filter_range.latte';


	/**
	 * @param string $key
	 * @param string $name
	 * @param string $column
	 * @param string $name_second
	 */
	public function __construct($key, $name, $column, $name_second)
	{
		parent::__construct($key, $name, $column);

		$this->name_second = $name_second;
	}


	/**
	 * Adds select box to filter form
	 * @param Nette\Forms\Container $container
	 */
	public function addToFormContainer($container)
	{
		$container = $container->addContainer($this->key);

		$container->addText('from', $this->name);

		$container->addText('to', $this->name_second);

		if ($placeholder_array = $this->getPlaceholder()) {
			$text_from = reset($placeholder_array);

			if ($text_from) {
				$container[$this->key]['from']->setAttribute('placeholder', $text_from);
			}

			$text_to = end($placeholder_array);

			if ($text_to && ($text_to != $text_from)) {
				$container[$this->key]['to']->setAttribute('placeholder', $text_to);
			}
		}
	}


	/**
	 * Set html attr placeholder of both inputs
	 * @param string $placeholder_array
	 * @return static
	 */
	public function setPlaceholder($placeholder_array)
	{
		if (!is_array($placeholder_array)) {
			throw new DataGridFilterRangeException(
				'FilterRange::setPlaceholder can only accept array of placeholders'
			);
		}

		$this->placeholder_array = $placeholder_array;

		return $this;
	}


	/**
	 * Get filter condition
	 * @return array
	 */
	public function getCondition()
	{
		$value = $this->getValue();

		return [$this->column => [
			'from' => isset($value['from']) ? $value['from'] : '',
			'to' => isset($value['to']) ? $value['to'] : ''
		]];
	}

}
