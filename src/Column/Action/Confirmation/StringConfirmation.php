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
	 * @var string|null
	 */
	private $placeholderName;


	public function __construct(string $question, ?string $placeholderName = null)
	{
		$this->question = $question;
		$this->placeholderName = $placeholderName;
	}


	public function getQuestion(): string
	{
		return $this->question;
	}


	public function getPlaceholderName(): ?string
	{
		return $this->placeholderName;
	}
}
