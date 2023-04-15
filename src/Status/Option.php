<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Status;

use Contributte\Datagrid\Column\Action\Confirmation\CallbackConfirmation;
use Contributte\Datagrid\Column\Action\Confirmation\IConfirmation;
use Contributte\Datagrid\Column\Action\Confirmation\StringConfirmation;
use Contributte\Datagrid\Column\ColumnStatus;
use Contributte\Datagrid\Datagrid;
use Contributte\Datagrid\Exception\DatagridException;
use Contributte\Datagrid\Row;

class Option
{

	protected ?string $title = null;

	protected string $class = 'btn-success';

	protected string $classSecondary = 'btn btn-xs';

	protected string $classInDropdown = 'ajax dropdown-item';

	protected ?string $icon = null;

	protected ?string $iconSecondary = null;

	protected ?IConfirmation $confirmation = null;

	public function __construct(private Datagrid $grid, protected ColumnStatus $columnStatus, protected mixed $value, protected string $text)
	{
	}

	public function getValue(): mixed
	{
		return $this->value;
	}

	public function endOption(): ColumnStatus
	{
		return $this->columnStatus;
	}

	/**
	 * @return static
	 */
	public function setTitle(string $title): self
	{
		$this->title = $title;

		return $this;
	}

	public function getTitle(): ?string
	{
		return $this->title;
	}

	/**
	 * @return static
	 */
	public function setClass(string $class, ?string $classSecondary = null): self
	{
		$this->class = $class;

		if ($classSecondary !== null) {
			$this->classSecondary = $classSecondary;
		}

		return $this;
	}

	public function getClass(): ?string
	{
		return $this->class;
	}

	/**
	 * @return static
	 */
	public function setClassSecondary(string $classSecondary): self
	{
		$this->classSecondary = $classSecondary;

		return $this;
	}

	public function getClassSecondary(): string
	{
		return $this->classSecondary;
	}

	/**
	 * @return static
	 */
	public function setClassInDropdown(string $classInDropdown): self
	{
		$this->classInDropdown = $classInDropdown;

		return $this;
	}

	public function getClassInDropdown(): string
	{
		return $this->classInDropdown;
	}

	/**
	 * @return static
	 */
	public function setIcon(string $icon): self
	{
		$this->icon = $icon;

		return $this;
	}

	public function getIcon(): ?string
	{
		return $this->icon;
	}

	/**
	 * @return static
	 */
	public function setIconSecondary(string $iconSecondary): self
	{
		$this->iconSecondary = $iconSecondary;

		return $this;
	}

	public function getIconSecondary(): ?string
	{
		return $this->iconSecondary;
	}

	public function getText(): string
	{
		return $this->text;
	}

	/**
	 * @return static
	 */
	public function setConfirmation(IConfirmation $confirmation): self
	{
		$this->confirmation = $confirmation;

		return $this;
	}

	public function getConfirmationDialog(Row $row): ?string
	{
		if ($this->confirmation === null) {
			return null;
		}

		if ($this->confirmation instanceof CallbackConfirmation) {
			return ($this->confirmation->getCallback())($row->getItem());
		}

		if ($this->confirmation instanceof StringConfirmation) {
			$question = $this->grid->getTranslator()->translate($this->confirmation->getQuestion());

			if ($this->confirmation->getPlaceholderName() === null) {
				return $question;
			}

			return str_replace(
				'%s',
				$row->getValue($this->confirmation->getPlaceholderName()),
				$question
			);
		}

		throw new DatagridException('Unsupported confirmation');
	}

}
