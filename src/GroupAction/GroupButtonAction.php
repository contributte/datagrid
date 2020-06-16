<?php

declare(strict_types=1);

namespace Ublaboo\DataGrid\GroupAction;

class GroupButtonAction extends GroupAction
{

	/**
	 * @var array|callable[]
	 */
	public $onClick = [];

	/**
	 * @var string
	 */
	protected $class = 'btn btn-sm btn-success';


	public function __construct(string $title, ?string $class = null)
	{
		parent::__construct($title);

		if (!is_null($class) && !empty($class)) {
			$this->class = $class;
		}
	}

}
