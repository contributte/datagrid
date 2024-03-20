<?php declare(strict_types = 1);

namespace Contributte\Datagrid\GroupAction;

class GroupSelectAction extends GroupAction
{

	public function __construct(string $title, protected array $options = [])
	{
		parent::__construct($title);
	}

	public function getOptions(): array
	{
		return $this->options;
	}

	/**
	 * Has the action some options?
	 */
	public function hasOptions(): bool
	{
		return $this->options !== [];
	}

}
