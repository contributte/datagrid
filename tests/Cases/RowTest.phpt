<?php declare(strict_types = 1);

namespace Ublaboo\DataGrid\Tests\Cases;

use LeanMapper;
use Nette\SmartObject;
use Nette\Utils\Html;
use Tester\Assert;
use Tester\TestCase;
use Ublaboo;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../Files/XTestingDataGridFactory.php';

final class RowTest extends TestCase
{

	/**
	 * @var Ublaboo\DataGrid\DataGrid
	 */
	private $grid;

	public function setUp(): void
	{
		$factory = new Ublaboo\DataGrid\Tests\Files\XTestingDataGridFactory();
		$this->grid = $factory->createXTestingDataGrid();
	}


	public function testControl(): void
	{
		$item = ['id' => 20, 'name' => 'John Doe'];
		$callback = function ($item, Html $row): void {
			$row->addClass('bg-warning');
		};

		$row = new Ublaboo\DataGrid\Row($this->grid, $item, 'id');
		$callback($item, $row->getControl());

		Assert::same(20, $row->getId());
		Assert::same('bg-warning', $row->getControlClass());
	}


	public function testArray(): void
	{
		$item = ['id' => 20, 'name' => 'John Doe'];

		$row = new Ublaboo\DataGrid\Row($this->grid, $item, 'id');

		Assert::same(20, $row->getId());
		Assert::same('John Doe', $row->getValue('name'));
	}


	public function testObject(): void
	{
		$item = (object) ['id' => 20, 'name' => 'John Doe'];

		$row = new Ublaboo\DataGrid\Row($this->grid, $item, 'id');

		Assert::same(20, $row->getId());
	}


	public function testLeanMapperEntity(): void
	{
		$entity = new XTestingLMDataGridEntity(['id' => 20, 'name' => 'John Doe', 'age' => 23]);
		$entity2 = new XTestingLMDataGridEntity2(['id' => 21, 'name' => 'Francis', 'age' => 20]);

		$entity->setGirlfriend($entity2);

		$row = new Ublaboo\DataGrid\Row($this->grid, $entity, 'id');

		Assert::same('John Doe', $row->getValue('name'));
		Assert::same(23, $row->getValue('age'));
		Assert::same(20, $row->getValue('girlfriend.age'));
	}


	public function testDoctrineEntity(): void
	{
		$entity = new XTestingDDataGridEntity(['id' => 20, 'name' => 'John Doe', 'age' => 23]);
		$entity2 = new XTestingDDataGridEntity(['id' => 21, 'name' => 'Francis', 'age' => 20]);

		$entity->setPartner($entity2);

		$row = new Ublaboo\DataGrid\Row($this->grid, $entity, 'id');

		Assert::same(20, $row->getId());
		Assert::same('John Doe', $row->getValue('name'));
		Assert::same(23, $row->getValue('age'));
		Assert::same(20, $row->getValue('partner.age'));
	}

}


/**
 * @property int $id
 * @property string $name
 * @property XTestingLMDataGridEntity2|NULL $girlfriend
 */
class XTestingLMDataGridEntity extends LeanMapper\Entity
{

	private $age;

	public function getAge()
	{
		return $this->age;
	}


	public function setAge($age): void
	{
		$this->age = $age;
	}

}


/**
 * @property int $id
 * @property string $name
 */
class XTestingLMDataGridEntity2 extends LeanMapper\Entity
{

	private $age;

	public function getAge()
	{
		return $this->age;
	}


	public function setAge($age): void
	{
		$this->age = $age;
	}

}


class XTestingDDataGridEntity
{

	use SmartObject;

	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue
	 * @var int
	 */
	private $id;

	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	private $name;

	/**
	 * @ORM\Column(type="integer")
	 * @var int
	 */
	private $age;

	private $partner;

	public function __construct($args)
	{
		$this->id = $args['id'];
		$this->age = $args['age'];
		$this->name = $args['name'];
	}


	public function getName()
	{
		return $this->name;
	}


	/**
	 * @return int
	 */
	final public function getId(): int
	{
		return $this->id;
	}


	public function getAge()
	{
		return $this->age;
	}


	public function setPartner($p): void
	{
		$this->partner = $p;
	}


	public function getPartner()
	{
		return $this->partner;
	}

}


$test_case = new RowTest();
$test_case->run();
