<?php declare(strict_types = 1);

namespace Ublaboo\DataGrid\Column;

use Nette\Utils\Html;
use Ublaboo\DataGrid\Column\Action\Confirmation\CallbackConfirmation;
use Ublaboo\DataGrid\Column\Action\Confirmation\IConfirmation;
use Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Exception\DataGridColumnRendererException;
use Ublaboo\DataGrid\Exception\DataGridException;
use Ublaboo\DataGrid\Row;
use Ublaboo\DataGrid\Traits\TButtonText;
use Ublaboo\DataGrid\Traits\TButtonTryAddIcon;
use Ublaboo\DataGrid\Traits\TLink;
use Ublaboo\DataGrid\Traits\TRenderCondition;

class Action extends Column
{

	use TButtonTryAddIcon;
	use TButtonText;
	use TLink;
	use TRenderCondition;

	public static string $dataConfirmAttributeName = 'datagrid-confirm';

	protected ?IConfirmation $confirmation = null;

	/** @var array */
	protected array $dataAttributes = [];

	/** @var array */
	protected array $attributes = [];

	/** @var array */
	protected array $parameters = [];

	/** @var string|callable|null */
	protected $icon;

	/** @var string|callable|null */
	protected $class = '';

	protected bool $openInNewTab = false;

	/** @var string|callable */
	private $title;

	public function __construct(
		DataGrid $grid,
		string $key,
		protected string $href,
		string $name,
		protected array $params
	)
	{
		parent::__construct($grid, $key, '', $name);

		$this->class = sprintf('btn btn-xs %s', $grid::$btnSecondaryClass);
	}

	public function render(Row $row): mixed
	{
		if (!$this->shouldBeRendered($row)) {
			return null;
		}

		try {
			return $this->useRenderer($row);
		} catch (DataGridColumnRendererException) {
			// No need to worry.
		}

		$link = $this->createLink(
			$this->grid,
			$this->href,
			$this->getItemParams($row, $this->params) + $this->parameters
		);

		$a = Html::el('a')->href($link);

		$this->tryAddIcon($a, $this->getIcon($row), $this->getName());

		if ($this->dataAttributes !== []) {
			foreach ($this->dataAttributes as $key => $value) {
				$a->data((string) $key, $value);
			}
		}

		if ($this->attributes !== []) {
			$a->addAttributes($this->attributes);
		}

		$a->addText($this->translate($this->getName()));

		$title = $this->getTitle($row);

		if ($title !== null) {
			$a->setAttribute('title', $this->translate($title));
		}

		if ($this->class !== null) {
			$a->setAttribute('class', $this->getClass($row));
		}

		$confirmationDialog = $this->getConfirmationDialog($row);

		if ($confirmationDialog !== null) {
			$a->data(static::$dataConfirmAttributeName, $confirmationDialog);
		}

		if ($this->openInNewTab) {
			$a->addAttributes(['target' => '_blank']);
		}

		return $a;
	}

	/**
	 * @return static
	 */
	public function addParameters(array $parameters): self
	{
		$this->parameters = $parameters;

		return $this;
	}

	/**
	 * @return static
	 * @throws DataGridException
	 */
	public function setTitle(string|callable $title): self
	{
		$this->checkPropertyStringOrCallable($title, 'title');

		$this->title = $title;

		return $this;
	}

	/**
	 * @throws DataGridException
	 */
	public function getTitle(Row $row): ?string
	{
		/**
		 * If user callback was used for setting action title, it has to return string
		 */
		return $this->getPropertyStringOrCallableGetString($row, $this->title, 'title');
	}

	/**
	 * @return static
	 * @throws DataGridException
	 */
	public function setClass(null|string|callable $class): self
	{
		$this->checkPropertyStringOrCallable($class, 'class');

		$this->class = $class;

		return $this;
	}

	/**
	 * @throws DataGridException
	 */
	public function getClass(Row $row): ?string
	{
		/**
		 * If user callback was used for setting action class, it has to return string
		 */
		return $this->getPropertyStringOrCallableGetString($row, $this->class, 'class');
	}

	/**
	 * @return static
	 * @throws DataGridException
	 */
	public function setIcon(string|callable $icon): self
	{
		$this->checkPropertyStringOrCallable($icon, 'icon');

		$this->icon = $icon;

		return $this;
	}

	/**
	 * @throws DataGridException
	 */
	public function getIcon(Row $row): ?string
	{
		/**
		 * If user callback was used for setting action icon, it has to return string
		 */
		return $this->getPropertyStringOrCallableGetString($row, $this->icon, 'icon');
	}

	/**
	 * @return static
	 */
	public function setConfirmation(IConfirmation $confirmation): self
	{
		$this->confirmation = $confirmation;

		return $this;
	}

	/**
	 * @throws DataGridException
	 */
	public function getConfirmationDialog(Row $row): ?string
	{
		if ($this->confirmation === null) {
			return null;
		}

		if ($this->confirmation instanceof CallbackConfirmation) {
			return ($this->confirmation->getCallback())($row->getItem());
		}

		if ($this->confirmation instanceof StringConfirmation) {
			$question = $this->translate($this->confirmation->getQuestion());

			if ($this->confirmation->getPlaceholderName() === null) {
				return $question;
			}

			return str_replace(
				'%s',
				(string) $row->getValue($this->confirmation->getPlaceholderName()),
				$question
			);
		}

		throw new DataGridException('Unsupported confirmation');
	}

	/**
	 * @return static
	 */
	public function setDataAttribute(string $key, mixed $value): self
	{
		$this->dataAttributes[$key] = $value;

		return $this;
	}

	/**
	 * @return static
	 */
	public function addAttributes(array $attrs): self
	{
		$this->attributes += $attrs;

		return $this;
	}

	/**
	 * @throws DataGridException
	 */
	public function getPropertyStringOrCallableGetString(
		Row $row,
		string|callable|null $property,
		string $name
	): ?string
	{
		if (is_string($property)) {
			return $property;
		}

		if (is_callable($property)) {
			$value = call_user_func($property, $row->getItem());

			if (!is_string($value)) {
				throw new DataGridException(sprintf('Action %s callback has to return a string', $name));
			}

			return $value;
		}

		return $property;
	}

	public function isOpenInNewTab(): bool
	{
		return $this->openInNewTab;
	}

	/**
	 * @return static
	 */
	public function setOpenInNewTab(bool $openInNewTab = true): self
	{
		$this->openInNewTab = $openInNewTab;

		return $this;
	}

	/**
	 * @throws DataGridException
	 */
	protected function checkPropertyStringOrCallable(mixed $property, string $name): void
	{
		if (!is_string($property) && !is_callable($property) && $property !== null) {
			throw new DataGridException(
				sprintf('Action %s has to be either string or a callback returning string', $name)
			);
		}
	}

	protected function translate(string $message): string
	{
		return $this->grid->getTranslator()->translate($message);
	}

}
