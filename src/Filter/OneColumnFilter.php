<?php declare(strict_types=1);

/**
 * @copyright   Copyright (c) 2015 ublaboo <ublaboo@paveljanda.com>
 * @author      Pavel Janda <me@paveljanda.com>
 * @package     Ublaboo
 */

namespace Ublaboo\DataGrid\Filter;

use Nette;
use Nette\Forms\Controls\BaseControl;
use Ublaboo\DataGrid\DataGrid;

abstract class OneColumnFilter extends Filter
{

	/**
	 * @var string
	 */
	protected $column;


	public function __construct(
		DataGrid $grid,
		string $key,
		string $name,
		string $column
	) {
		parent::__construct($grid, $key, $name);

		$this->column = $column;
	}


	/**
	 * Get filter column
	 */
	public function getColumn(): string
	{
		return $this->column;
	}


	public function getCondition(): array
	{
		return [$this->column => $this->getValue()];
	}
}
