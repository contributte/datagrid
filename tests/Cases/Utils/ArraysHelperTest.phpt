<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Tests\Cases\Utils;

use Contributte\Datagrid\Utils\ArraysHelper;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../../bootstrap.php';

final class ArraysHelperTest extends TestCase
{

	public function testEmptyRecognizesNestedEmptyValues(): void
	{
		Assert::true(ArraysHelper::testEmpty([]));
		Assert::true(ArraysHelper::testEmpty([null, '', [null, '']]));
	}

	public function testEmptyKeepsZeroAndFalseAsValues(): void
	{
		Assert::false(ArraysHelper::testEmpty([0]));
		Assert::false(ArraysHelper::testEmpty(['0']));
		Assert::false(ArraysHelper::testEmpty([false]));
		Assert::false(ArraysHelper::testEmpty([[null, [false]]]));
	}

	public function testTruthyIgnoresOnlyNullAndEmptyString(): void
	{
		Assert::false(ArraysHelper::testTruthy([]));
		Assert::false(ArraysHelper::testTruthy([null, '', [null, '']]));

		Assert::true(ArraysHelper::testTruthy([0]));
		Assert::true(ArraysHelper::testTruthy(['0']));
		Assert::true(ArraysHelper::testTruthy([false]));
		Assert::true(ArraysHelper::testTruthy([[null, [false]]]));
	}

}


(new ArraysHelperTest())->run();
