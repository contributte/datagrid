<?php declare(strict_types = 1);

namespace Contributte\Datagrid\GroupAction;

/**
 * @method void onClick(array $ids)
 */
class GroupButtonAction extends GroupAction
{

	/** @var array|callable[] */
	public array $onClick = [];

	protected string $class = 'btn btn-sm btn-success';

	public function __construct(string $title, ?string $class = null)
	{
		parent::__construct($title);

		if ($class !== null && $class !== '') {
			$this->class = $class;
		}
	}

}
