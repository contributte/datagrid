<?php

declare(strict_types=1);

namespace Ublaboo\DataGrid\Column\Action\Confirmation;

use Nette\Localization\ITranslator;

final class StringConfirmation implements IConfirmation
{

	/**
	 * @var string
	 */
	private $question;

	/**
	 * @var string[]
	 */
	private $placeholders;

	/**
	 * @var ITranslator|null
	 */
	private $translator;


	public function __construct(string $question, string ...$placeholders)
	{
		$this->question = $question;
		$this->placeholders = $placeholders;
	}


	public function getQuestion(): string
	{
		return $this->translator !== null ? $this->translator->translate($this->question) : $this->question;
	}


	public function havePlaceholders(): bool
	{
		return (bool) $this->placeholders;
	}


	public function getPlaceholders(): array
	{
		return $this->placeholders;
	}


	public function getTranslator(): ?ITranslator
	{
		return $this->translator;
	}


	public function setTranslator(?ITranslator $translator): void
	{
		$this->translator = $translator;
	}
}
