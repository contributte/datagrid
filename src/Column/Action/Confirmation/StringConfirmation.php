<?php

declare(strict_types=1);

namespace Ublaboo\DataGrid\Column\Action\Confirmation;

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


	public function __construct(string $question, string ...$placeholders)
	{
		$this->question = $question;
		$this->placeholders = $placeholders;
	}


	public function getQuestion(): string
	{
		return $this->question;
	}


	public function havePlaceholders(): bool
	{
		return (bool) $this->placeholders;
	}


	public function getPlaceholders(): array
	{
		return $this->placeholders;
	}
}
