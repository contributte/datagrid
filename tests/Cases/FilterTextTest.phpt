<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Tests\Cases;

use Contributte\Datagrid\Filter\FilterText;
use Contributte\Datagrid\Tests\Files\TestingDatagridFactory;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../bootstrap.php';

final class FilterTextTest extends TestCase
{

	public function testLegacyTermsRemainUnchanged(): void
	{
		$filter = $this->createFilter();
		Assert::false($filter->hasQuotedPhraseSearch());
		foreach (['foo  bar', '"red blue" paint', "a\tb", '', '0'] as $value) {
			Assert::same(explode(' ', $value), $filter->getSearchTerms($value));
		}

		$filter->setQuotedPhraseSearch();
		Assert::true($filter->hasQuotedPhraseSearch());
		$filter->setQuotedPhraseSearch(false);
		Assert::false($filter->hasQuotedPhraseSearch());
		Assert::same(['"red', 'blue"'], $filter->getSearchTerms('"red blue"'));
	}

	public function testQuotedTerms(): void
	{
		$filter = $this->createFilter()->setQuotedPhraseSearch();
		foreach ([
			['"red blue" paint', ['red blue', 'paint']],
			["  red\t blue\npaint  ", ['red', 'blue', 'paint']],
			['"červená modrá" žluťoučký', ['červená modrá', 'žluťoučký']],
			["O'Reilly \"New York\"", ["O'Reilly", 'New York']],
			['"0" 0', ['0', '0']],
			['"" red ""', ['red']],
			['" "', [' ']],
			['""', ['']],
			['   ', ['']],
			['', ['']],
			['red "blue paint', ['red', 'blue paint']],
			['pre"red blue"post', ['prered bluepost']],
			['"red\\" blue"', ['red" blue']],
			['red\\"blue', ['red"blue']],
			['C:\\path\\file', ['C:\\path\\file']],
			['"C:\\\\path"', ['C:\\path']],
			['path\\', ['path\\']],
		] as [$input, $expected]) {
			Assert::same($expected, $filter->getSearchTerms($input));
		}
	}

	public function testWholeInputSettingsTakePrecedence(): void
	{
		$filter = $this->createFilter()->setQuotedPhraseSearch()->setSplitWordsSearch(false);
		Assert::same(['"red blue" paint'], $filter->getSearchTerms('"red blue" paint'));
		Assert::same(['0'], $filter->getSearchTerms('0'));
		$filter->setSplitWordsSearch(true)->setExactSearch();
		Assert::same(['"red blue" paint'], $filter->getSearchTerms('"red blue" paint'));
	}

	public function testTermsDoNotReplaceRawValues(): void
	{
		$filter = $this->createFilter()->setQuotedPhraseSearch();
		$filter->setValue('"red blue" paint');
		Assert::same(['red blue', 'paint'], $filter->getSearchTerms($filter->getValue()));
		Assert::same('"red blue" paint', $filter->getValue());
		Assert::same(['name' => '"red blue" paint'], $filter->getCondition());
	}

	private function createFilter(): FilterText
	{
		return new FilterText((new TestingDatagridFactory())->createTestingDatagrid(), 'search', 'Search', ['name']);
	}

}

(new FilterTextTest())->run();
