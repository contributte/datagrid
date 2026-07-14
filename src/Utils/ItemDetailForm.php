<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Utils;

use Nette\Application\UI\Presenter;
use Nette\ComponentModel\IComponent;
use Nette\Forms\Container;
use Nette\Forms\Form;
use Nette\Utils\Arrays;
use UnexpectedValueException;

final class ItemDetailForm extends Container
{

	/** @var array<callable(Container, mixed): void> */
	public array $onSetDefaults = [];

	/** @var callable */
	private $callableSetContainer;

	private mixed $httpPost = null;

	/** @var array<bool> */
	private array $containerSetByName = [];

	private mixed $item = null;

	public function __construct(callable $callableSetContainer)
	{
		$this->monitor(
			Presenter::class,
			function (Presenter $presenter): void {
				$this->loadHttpData();
			}
		);

		$this->callableSetContainer = $callableSetContainer;
	}

	public function offsetGet(mixed $name): IComponent
	{
		return $this->getComponentAndSetContainer($name);
	}

	public function getComponentAndSetContainer(mixed $name): IComponent
	{
		$container = $this->addContainer($name);

		if (!isset($this->containerSetByName[$name])) {
			call_user_func($this->callableSetContainer, $container);

			$this->containerSetByName[$name] = true;
		}

		$this->applyDefaults($container);

		return $container;
	}

	public function setItem(mixed $item): void
	{
		$this->item = $item;
	}

	/**
	 * @throws UnexpectedValueException
	 */
	private function getHttpData(): mixed
	{
		if ($this->httpPost === null) {
			$lookupPath = $this->lookupPath(Form::class);
			$form = $this->getForm();

			$path = explode(self::NameSeparator, $lookupPath);

			$httpData = $form->getHttpData();

			if (!is_array($httpData)) {
				$httpData = [];
			}

			$this->httpPost = Arrays::get($httpData, $path, null);
		}

		return $this->httpPost;
	}

	/**
	 * @throws UnexpectedValueException
	 */
	private function loadHttpData(): void
	{
		$form = $this->getForm();

		if ($form->isSubmitted() === false) {
			return;
		}

		foreach ((array) $this->getHttpData() as $name => $value) {
			if ((is_iterable($value))) {
				$this->getComponentAndSetContainer($name);
			}
		}
	}

	private function applyDefaults(Container $container): void
	{
		if ($this->item === null) {
			return;
		}

		if ($this->getForm()->isSubmitted() !== false) {
			return;
		}

		foreach ($this->onSetDefaults as $onSetDefaults) {
			$onSetDefaults($container, $this->item);
		}
	}

}
