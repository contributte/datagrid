<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\Utils;

use Nette;
use Ublaboo\DataGrid\Exception\DataGridDateTimeHelperException;

final class DateTimeHelper extends Nette\Object
{

	/**
	 * Try to convert string into DateTime object
	 * @param  string $value
	 * @return \DateTime
	 * @throws DataGridDateTimeHelperException
	 */
	public static function tryConvertToDateTime($value)
	{
		/**
		 * Try to convert string Y-m-d to DateTime object
		 */
		$date = \DateTime::createFromFormat('Y-m-d', $value);

		if ($date) {
			return $date;
		}

		/**
		 * Try to convert string Y-m-d H:i:s to DateTime object
		 */
		$date = \DateTime::createFromFormat('Y-m-d H:i:s', $value);

		if ($date) {
			return $date;
		}

		/**
		 * Try to convert string Y-m-d H:i:s.u to DateTime object
		 */
		$date = \DateTime::createFromFormat('Y-m-d H:i:s.u', $value);

		if ($date) {
			return $date;
		}

		/**
		 * Try strtotime
		 */
		$timestamp = strtotime($value);
		if (FALSE !== $timestamp) {
			$date = new \DateTime;
			$date->setTimestamp($timestamp);

			return $date;
		}

		/**
		 * Could not convert string to datatime
		 */
		throw new DataGridDateTimeHelperException;
	}

}
