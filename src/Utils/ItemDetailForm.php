<?php declare(strict_types = 1);

namespace Ublaboo\DataGrid\Utils;

use Nette;
use Nette\ComponentModel\IContainer;
use Nette\Forms\Container;
use Traversable;

final class ItemDetailForm extends Container
{

	/** @var callable */
	private $callableSetContainer;

	/** @var array */
	private $http_post;

	public function __construct(callable $callableSetContainer)
	{
		parent::__construct();

		$this->monitor('Nette\Application\UI\Presenter');

		$this->callableSetContainer = $callableSetContainer;
	}


	protected function attached(IContainer $presenter): void
	{
		parent::attached($presenter);

		if (!$presenter instanceof Nette\Application\UI\Presenter) {
			return;
		}

		$this->loadHttpData();
	}


	public function loadHttpData(): void
	{
		if (!$this->getForm()->isSubmitted()) {
			return;
		}

		foreach ((array) $this->getHttpData() as $name => $value) {
			if ((is_array($value) || $value instanceof Traversable) && !$this->getComponent($name, false)) {
				$this->getComponent($name);
			}
		}
	}


	/**
	 * @return mixed|null
	 */
	private function getHttpData()
	{
		if ($this->http_post === null) {
			$path = explode(self::NAME_SEPARATOR, $this->lookupPath('Nette\Forms\Form'));

			$this->http_post = Nette\Utils\Arrays::get($this->getForm()->getHttpData(), $path, null);
		}

		return $this->http_post;
	}


	public function offsetGet(string $name): Container
	{
		return $this->getComponent($name);
	}


	public function getComponent(string $name, bool $throw = true): Container
	{
		$container = $this->addContainer($name);

		call_user_func($this->callableSetContainer, $container);

		return $container;
	}

}
