<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Tests\Cases;

require __DIR__ . '/../bootstrap.php';

use Contributte\Datagrid\Datagrid;
use Contributte\Datagrid\Tests\Files\TestingDatagridFactoryRouter;
use Nette\Forms\Container;
use Nette\Forms\Controls\TextArea;
use Nette\Utils\ArrayHash;
use Tester\Assert;
use Tester\TestCase;

final class ItemDetailFormTest extends TestCase
{

	/**
	 * Regression test for https://github.com/contributte/datagrid/issues/962
	 *
	 * A textarea inside the item detail form cannot receive its value via the
	 * {input name, value => ...} attribute (that only works for <input>). The
	 * onSetDefaults callback sets the actual control value, so it works for any
	 * control type.
	 */
	public function testOnSetDefaultsFillsTextArea(): void
	{
		$factory = new TestingDatagridFactoryRouter();
		/** @var Datagrid $grid */
		$grid = $factory->createTestingDatagrid()->getComponent('grid');

		$grid->setItemsDetail();
		$grid->setItemsDetailForm(function (Container $container): void {
			$container->addTextArea('description');
		});

		$itemDetailForm = $grid->getItemDetailForm();
		Assert::notNull($itemDetailForm);

		$itemDetailForm->onSetDefaults[] = function (Container $container, $item): void {
			$container->setDefaults([
				'description' => $item->description,
			]);
		};

		$form = $grid->createComponentFilter();

		$item = ArrayHash::from(['id' => 1, 'description' => 'Lorem ipsum']);
		$form['items_detail_form']->setItem($item);

		$container = $form['items_detail_form']['items_detail_form_1'];
		Assert::type(Container::class, $container);

		$description = $container['description'];
		Assert::type(TextArea::class, $description);
		Assert::same('Lorem ipsum', $description->getValue());
	}

	public function testWithoutItemDefaultsAreNotApplied(): void
	{
		$factory = new TestingDatagridFactoryRouter();
		/** @var Datagrid $grid */
		$grid = $factory->createTestingDatagrid()->getComponent('grid');

		$grid->setItemsDetail();
		$grid->setItemsDetailForm(function (Container $container): void {
			$container->addTextArea('description');
		});

		$itemDetailForm = $grid->getItemDetailForm();
		Assert::notNull($itemDetailForm);

		$itemDetailForm->onSetDefaults[] = function (Container $container, $item): void {
			$container['description']->setValue('should not appear');
		};

		$form = $grid->createComponentFilter();

		// No setItem() call — defaults must not be applied, controls stay empty.
		$container = $form['items_detail_form']['items_detail_form_1'];
		Assert::same('', $container['description']->getValue());
	}

}

(new ItemDetailFormTest())->run();
