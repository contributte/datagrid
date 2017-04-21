<?php

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\AggregationFunction;

interface IAggregatable
{
	/**
	 * @param  callable  $aggregationCallback
	 * @return void
	 */
	public function processAggregation(callable $aggregationCallback);
}
