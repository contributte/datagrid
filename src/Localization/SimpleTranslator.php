<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\Localization;

use Nette;
use Nette\SmartObject;

class SimpleTranslator implements Nette\Localization\ITranslator
{

	use SmartObject;

	/**
	 * @var array
	 */
	private $dictionary = [
		'ublaboo_datagrid.no_item_found_reset' => 'No items found. You can reset the filter',
		'ublaboo_datagrid.no_item_found' => 'No items found.',
		'ublaboo_datagrid.here' => 'here',
		'ublaboo_datagrid.items' => 'Items',
		'ublaboo_datagrid.all' => 'all',
		'ublaboo_datagrid.from' => 'from',
		'ublaboo_datagrid.reset_filter' => 'Reset filter',
		'ublaboo_datagrid.group_actions' => 'Group actions',
		'ublaboo_datagrid.show' => 'Show',
		'ublaboo_datagrid.add' => 'Add',
		'ublaboo_datagrid.edit' => 'Edit',
		'ublaboo_datagrid.show_all_columns' => 'Show all columns',
		'ublaboo_datagrid.show_default_columns' => 'Show default columns',
		'ublaboo_datagrid.hide_column' => 'Hide column',
		'ublaboo_datagrid.action' => 'Action',
		'ublaboo_datagrid.previous' => 'Previous',
		'ublaboo_datagrid.next' => 'Next',
		'ublaboo_datagrid.choose' => 'Choose',
		'ublaboo_datagrid.choose_input_required' => 'Group action text not allow empty value',
		'ublaboo_datagrid.execute' => 'Execute',
		'ublaboo_datagrid.save' => 'Save',
		'ublaboo_datagrid.cancel' => 'Cancel',
		'ublaboo_datagrid.multiselect_choose' => 'Choose',
		'ublaboo_datagrid.multiselect_selected' => '{0} selected',
		'ublaboo_datagrid.filter_submit_button' => 'Filter',
		'ublaboo_datagrid.show_filter' => 'Show filter',
		'ublaboo_datagrid.per_page_submit' => 'Change',
	];


	/**
	 * @param array $dictionary
	 */
	public function __construct($dictionary = null)
	{
		if (is_array($dictionary)) {
			$this->dictionary = $dictionary;
		}
	}


	/**
	 * Translates the given string
	 * 
	 * @param  string
	 * @param  int
	 * @return string
	 */
	public function translate($message, $count = null)
	{
		return isset($this->dictionary[$message]) ? $this->dictionary[$message] : $message;
	}


	/**
	 * Set translator dictionary
	 * @param array $dictionary
	 */
	public function setDictionary(array $dictionary)
	{
		$this->dictionary = $dictionary;
	}
}
