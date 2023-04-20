<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Localization;

use Nette\Localization\Translator;

class SimpleTranslator implements Translator
{

	private array $dictionary = [
		'contributte_datagrid.no_item_found_reset' => 'No items found. You can reset the filter',
		'contributte_datagrid.no_item_found' => 'No items found.',
		'contributte_datagrid.here' => 'here',
		'contributte_datagrid.items' => 'Items',
		'contributte_datagrid.all' => 'all',
		'contributte_datagrid.from' => 'from',
		'contributte_datagrid.reset_filter' => 'Reset filter',
		'contributte_datagrid.group_actions' => 'Group actions',
		'contributte_datagrid.show' => 'Show',
		'contributte_datagrid.add' => 'Add',
		'contributte_datagrid.edit' => 'Edit',
		'contributte_datagrid.show_all_columns' => 'Show all columns',
		'contributte_datagrid.show_default_columns' => 'Show default columns',
		'contributte_datagrid.hide_column' => 'Hide column',
		'contributte_datagrid.action' => 'Action',
		'contributte_datagrid.previous' => 'Previous',
		'contributte_datagrid.next' => 'Next',
		'contributte_datagrid.choose' => 'Choose',
		'contributte_datagrid.choose_input_required' => 'Group action text not allow empty value',
		'contributte_datagrid.execute' => 'Execute',
		'contributte_datagrid.save' => 'Save',
		'contributte_datagrid.cancel' => 'Cancel',
		'contributte_datagrid.multiselect_choose' => 'Choose',
		'contributte_datagrid.multiselect_selected' => '{0} selected',
		'contributte_datagrid.filter_submit_button' => 'Filter',
		'contributte_datagrid.show_filter' => 'Show filter',
		'contributte_datagrid.per_page_submit' => 'Change',
	];

	public function __construct(array $dictionary = [])
	{
		// BC support for 'ublaboo' translations
		$oldPrefix = 'ublaboo_';
		$newPrefix = 'contributte_';
		foreach ($dictionary as $key => $value) {
			if (str_starts_with($key, $oldPrefix)) {
				$newKey = $newPrefix . substr($key, strlen($oldPrefix));
				// Only change the keys that are in the default $dictionary (other keys are left as-is)
				if (array_key_exists($newKey, $this->dictionary)) {
					trigger_error(sprintf("Translation key '%s' is deprecated, please use '%s' instead", $key, $newKey), E_USER_DEPRECATED);
					unset($dictionary[$key]);
					$dictionary[$newKey] = $value;
				}
			}
		}

		$this->dictionary = array_merge($this->dictionary, $dictionary);
	}

	public function translate(mixed $message, mixed ...$parameters): string
	{
		return $this->dictionary[$message] ?? $message;
	}

	public function setDictionary(array $dictionary): void
	{
		$this->dictionary = $dictionary;
	}

}
