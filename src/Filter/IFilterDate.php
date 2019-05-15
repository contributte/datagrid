<?php

declare(strict_types=1);

namespace Ublaboo\DataGrid\Filter;

interface IFilterDate
{

	/**
	 * Set format for datepicker etc
	 */
	public function setFormat(string $phpFormat, string $jsFormat): IFilterDate;


	/**
	 * Get php format for datapicker
	 */
	public function getPhpFormat(): string;


	/**
	 * Get js format for datepicker
	 */
	public function getJsFormat(): string;
}
