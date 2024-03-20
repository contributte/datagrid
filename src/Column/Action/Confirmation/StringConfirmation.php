<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Column\Action\Confirmation;

final class StringConfirmation implements IConfirmation
{

	public function __construct(private string $question, private ?string $placeholderName = null)
	{
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
