<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\Column;

use DateTime;
use Ublaboo\DataGrid\Row;

class ColumnDateTime extends Column
{

	/**
	 * @var string
	 */
	protected $format = 'j. n. Y';


	/**
	 * Format row item value as DateTime
	 * @param  Row $row
	 * @return string
	 */
	public function getColumnValue(Row $row)
	{
		$value = parent::getColumnValue($row);

		if (!($value instanceof DateTime)) {
			/**
			 * Try to convert string Y-m-d to DateTime object
			 */
			$date = \DateTime::createFromFormat('Y-m-d', $value);
			if ($date) {
				return $date->format($this->format);
			}

			/**
			 * Try to convert string Y-m-d H:i:s to DateTime object
			 */
			$date = \DateTime::createFromFormat('Y-m-d H:i:s', $value);
			if ($date) {
				return $date->format($this->format);
			}

			/**
			 * Try to convert string Y-m-d H:i:s.u to DateTime object
			 */
			$date = \DateTime::createFromFormat('Y-m-d H:i:s.u', $value);
			if ($date) {
				return $date->format($this->format);
			}

			/**
			 * Try strtotime
			 */
			$timestamp = strtotime($value);
			if (FALSE !== $timestamp) {
				$date = new \DateTime;
				$date->setTimestamp($timestamp);

				return $date->format($this->format);
			}

			/**
			 * Otherwise just return raw string
			 */
			return $value;
		}

		return $value->format($this->format);
	}


	/**
	 * Set DateTime format
	 * @param string $format
	 * @return static
	 */
	public function setFormat($format)
	{
		$this->format = $format;

		return $this;
	}

}
