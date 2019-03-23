<?php declare(strict_types=1);

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
	 */
	public function setFormat(string $phpFormat, string $jsFormat);


	/**
	 * Get php format for datapicker
	 */
	public function getPhpFormat(): string;


	/**
	 * Get js format for datepicker
	 */
	public function getJsFormat(): string;
}
