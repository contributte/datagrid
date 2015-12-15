<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\Column;

use DateTime;

class ColumnDateTime extends Column
{

	protected $format = 'j. n. Y';


	public function getColumnValue($item)
	{
		$value = parent::getColumnValue($item);

		if (!($value instanceof DateTime)) {
			/**
			 * Try to convert string Y-m-d to DateTime object
			 */
			$date = \DateTime::createFromFormat('Y-m-d', $value);

			if ($date) {
				return $date->format($this->format);
			}

			/**
			 * Try to convert string Y-m-d to DateTime object
			 */
			$date = \DateTime::createFromFormat('Y-m-d H:i:s', $value);

			if ($date) {
				return $date->format($this->format);
			}

			/**
			 * Otherwise just return raw string
			 */
			return $value;
		}

		return $value->format($this->format);
	}


	public function setFormat($format)
	{
		$this->format = $format;

		return $this;
	}

}
