<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\Filter;

interface IFilterDate
{

	/**
	 * Set format for datepicker etc
	 * @param string $php_format
	 * @param string $js_format
	 */
	public function setFormat($php_format, $js_format);

	/**
	 * Get php format for datapicker
	 * @return string
	 */
	public function getPhpFormat();

	/**
	 * Get js format for datepicker
	 * @return string
	 */
	public function getJsFormat();
}
