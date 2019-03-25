<?php declare(strict_types = 1);

namespace Ublaboo\DataGrid\Utils;

use Nette\Application\UI\Presenter;
use Nette\ComponentModel\IComponent;
use Nette\Forms\Container;
use Nette\Utils\Arrays;
use Traversable;

final class ItemDetailForm extends Container
{

	/** @var callable */
	private $callableSetContainer;

	/** @var array */
	private $httpPost;

	/** @var array|bool */
	private $containerSetByName = [];

	public function __construct(callable $callableSetContainer)
	{
		parent::__construct();

		$this->monitor(
			Presenter::class,
			function (Presenter $presenter): void {
				$this->loadHttpData();
			}
		);

		$this->callableSetContainer = $callableSetContainer;
	}


	/**
	 * @return mixed|null
	 */
	private function getHttpData()
	{
		if ($this->httpPost === null) {
			$path = explode(self::NAME_SEPARATOR, $this->lookupPath('Nette\Forms\Form'));

			$this->httpPost = Arrays::get($this->getForm()->getHttpData(), $path, null);
		}

		return $this->httpPost;
	}


	/**
	 * {@inheritDoc}
	 */
	public function offsetGet($name): IComponent
	{
		return $this->getComponentAndSetContainer($name);
	}


	/**
	 * @param string|int $name
	 */
	public function getComponentAndSetContainer($name): IComponent
	{
		$container = $this->addContainer($name);

		if (!isset($this->containerSetByName[$name])) {
			call_user_func($this->callableSetContainer, $container);

			$this->containerSetByName[$name] = true;
		}

		return $container;
	}


	private function loadHttpData(): void
	{
		if (!$this->getForm()->isSubmitted()) {
			return;
		}

		foreach ((array) $this->getHttpData() as $name => $value) {
			if ((is_array($value) || $value instanceof Traversable)) {
				$this->getComponentAndSetContainer($name);
			}
		}
	}

}
