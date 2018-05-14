<?php declare(strict_types = 1);

namespace Ublaboo\DataGrid\Filter;

interface IFilterDate
{

	/**
	 * Set format for datepicker etc
	 *
	 * @return static
	 */
	public function setFormat(string $php_format, string $js_format);

	/**
	 * Get php format for datapicker
	 */
	public function getPhpFormat(): string;

	/**
	 * Get js format for datepicker
	 */
	public function getJsFormat(): string;

}
