<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Tests\Cases;

use Contributte\Datagrid\Datagrid;
use Contributte\Datagrid\Export\Export;
use Contributte\Datagrid\Tests\Files\TestingDatagridFactory;
use Nette\Localization\Translator;
use Tester\Assert;
use Tester\TestCase;
use Tracy\Debugger;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../Files/TestingDatagridFactory.php';

final class ExportTest extends TestCase
{

	private Datagrid $grid;

	private array $data = [
		[
			'id' => 1,
			'name' => 'John Doe',
			'age' => 20,
		],
		[
			'id' => 2,
			'name' => 'Susie',
			'age' => 23,
		],
		[
			'id' => 3,
			'name' => 'Alexa',
			'age' => 19,
		],
		[
			'id' => 4,
			'name' => 'Alex',
			'age' => 22,
		],
	];

	public function setUp(): void
	{
		$factory = new TestingDatagridFactory();
		$this->grid = $factory->createTestingDatagrid();
	}

	public function testExportNotFiltered(): void
	{
		$data = $this->data;
		$callback = function ($source) use ($data): void {
			Assert::same($data, $source);
		};

		$this->grid->addExportCallback('Export', $callback);

		$trigger = function (): void {
			$this->grid->handleExport(1);
		};

		Assert::exception($trigger, 'Contributte\Datagrid\Exception\DatagridException', 'You have to set a data source first.');

		$this->grid->setDataSource($this->data);

		$this->grid->handleExport(1);
	}

	public function testExportFiltered(): void
	{
		$data = $this->data;
		$callback = function ($source) use ($data): void {
			Assert::same($data, $source);
		};

		$this->grid->addExportCallback('Export', $callback, true);

		$this->grid->addFilterText('name', 'Name');

		$this->grid;
		$trigger = function (): void {
			$this->grid->handleExport(1);
		};

		Assert::exception($trigger, 'Contributte\Datagrid\Exception\DatagridException', 'You have to set a data source first.');

		$this->grid->setDataSource($this->data);

		$this->grid->handleExport(1);
	}

	public function testRenderWithText(): void
	{
		$export = new Export($this->grid, 'Export', function (): void {
		}, false);

		Assert::same(
			'<a class="btn btn-xs btn-default btn-secondary" title="Export">Export</a>',
			(string) $export->render()
		);
	}

	public function testRenderIconWithoutText(): void
	{
		$this->grid->setTranslator(new class implements Translator {

			public function translate(mixed $message, mixed ...$parameters): string
			{
				return $message === '' ? '[empty]' : (string) $message;
			}

		});
		Datagrid::$iconPrefix = 'icon-';
		$export = new Export($this->grid, '', function (): void {
		}, false);
		$export->setIcon('download');
		$export->setTitle('Download');

		Assert::same(
			'<a class="btn btn-xs btn-default btn-secondary" title="Download"><i class="icon-download"></i>&nbsp;</a>',
			(string) $export->render()
		);
	}

}

Debugger::enable();

$test_case = new ExportTest();
$test_case->run();
