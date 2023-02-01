<?php

declare(strict_types=1);

namespace Ublaboo\DataGrid\Tests\Cases\DataSources;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\AST\PathExpression;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Tools\Setup;
use Ublaboo;
use Ublaboo\DataGrid\DataSource\DoctrineDataSource;

require __DIR__ . '/BaseDataSourceTest.phpt';

final class DoctrineDataSourceTest extends BaseDataSourceTest
{

	/**
	 * @var Connection
	 */
	private $db;

	public function setUp(): void
	{
		$this->setUpDatabase();

		$config = Setup::createAnnotationMetadataConfiguration([__DIR__ . '/tmp']);
		$entityManager = EntityManager::create($this->db, $config);

		$queryBuilder = $entityManager->getRepository('Ublaboo\\DataGrid\\Tests\\Cases\\DataSources\\User')->createQueryBuilder('e');

		$this->ds = new DoctrineDataSource($queryBuilder, 'id');
		$this->ds->setQueryHint(Query::HINT_CUSTOM_OUTPUT_WALKER, SortableNullsWalker::class);
		$this->ds->setQueryHint('sortableNulls.fields', [
			'e.name' => SortableNullsWalker::NULLS_LAST,
		]);

		$factory = new Ublaboo\DataGrid\Tests\Files\TestingDataGridFactory();
		$this->grid = $factory->createTestingDataGrid();
	}

	protected function setUpDatabase(): void
	{
		$config = new Configuration();

		$this->db = DriverManager::getConnection(['url' => 'sqlite:///:memory:'], $config);

		$this->db->executeQuery('CREATE TABLE users (
								id      INTEGER      PRIMARY KEY AUTOINCREMENT,
								name    VARCHAR (50),
								age     INTEGER (3),
								address VARCHAR (50)
							);
		');

		foreach ($this->data as $row) {
			$this->db->insert('users', $row);
		}
	}

}
/**
 * All properties are intentionally public so we can convert it to array in getActualResultAsArray
 *
 * @Entity @Table(name="users")
 **/
class User
{

	/**
	 * @Id @Column(type="integer") @GeneratedValue
	 **/
	public $id;

	/**
	 * @Column(type="string")
	 **/
	public $name;

	/**
	 * @Column(type="integer")
	 **/
	public $age;

	/**
	 * @Column(type="string")
	 **/
	public $address;

	public function getId()
	{
		return $this->id;
	}

	public function getName()
	{
		return $this->name;
	}

	public function setName($name): void
	{
		$this->name = $name;
	}

	public function getAge()
	{
		return $this->name;
	}

	public function setAge($name): void
	{
		$this->name = $name;
	}

	public function getAddress()
	{
		return $this->name;
	}

	public function setAddress($name): void
	{
		$this->name = $name;
	}

}

/**
 * @see https://gist.github.com/doctrinebot/ccd63ae93fb80415323d
 */
class SortableNullsWalker extends SqlWalker
{

	public const NULLS_FIRST = 'NULLS FIRST';
	public const NULLS_LAST = 'NULLS LAST';


	/**
	 * {@inheritDoc}
	 */
	public function walkOrderByItem($orderByItem)
	{
		$sql = parent::walkOrderByItem($orderByItem);
		$hint = $this->getQuery()->getHint('sortableNulls.fields');
		$expr = $orderByItem->expression;
		$type = strtoupper($orderByItem->type);

		if (!is_array($hint) || !count($hint)) {
			return $sql;
		}

		if (!$expr instanceof PathExpression || $expr->type !== PathExpression::TYPE_STATE_FIELD) {
			return $sql;
		}

		$index = $expr->identificationVariable.'.'.$expr->field;

		if (!isset($hint[$index])) {
			return $sql;
		}

		$search = $this->walkPathExpression($expr).' '.$type;
		$sql = str_replace($search, $search.' '.$hint[$index], $sql);

		return $sql;
	}
}

$test_case = new DoctrineDataSourceTest();
$test_case->run();
