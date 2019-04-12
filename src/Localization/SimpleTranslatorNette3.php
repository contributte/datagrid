<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\Localization;

use Nette;
use Nette\SmartObject;

class SimpleTranslatorNette3 extends BaseSimpleTranslator
{

	/**
	 * Translates the given string
	 *
	 * @param  string
	 */
	public function translate($message, ...$parameters): string
	{
		return isset($this->dictionary[$message]) ? $this->dictionary[$message] : $message;
	}

}
