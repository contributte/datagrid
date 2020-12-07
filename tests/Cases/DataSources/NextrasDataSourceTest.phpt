<?php

declare(strict_types=1);

namespace Ublaboo\DataGrid\Tests\Cases\DataSources;

use Nette\Caching\Cache;
use Nette\Caching\Storages\DevNullStorage;
use Nextras\Dbal\Connection;
use Nextras\Orm\Entity\Entity;
use Nextras\Orm\Mapper\Dbal\DbalMapperCoordinator;
use Nextras\Orm\Mapper\Mapper;
use Nextras\Orm\Model\Model;
use Nextras\Orm\Model\SimpleModelFactory;
use Nextras\Orm\Repository\Repository;
use Tester\Environment;
use Ublaboo\DataGrid\DataSource\NextrasDataSource;
use Ublaboo\DataGrid\Tests\Files\TestingDataGridFactory;

require __DIR__ . '/BaseDataSourceTest.phpt';

if (!extension_loaded('mysqli')) {
	Environment::skip('Test requires MySQLi extension to be loaded.');
}

/**
 * @dataProvider nextrasDatasource.ini
 */
final class NextrasDataSourceTest extends BaseDataSourceTest
{

	/**
	 * @var Model
	 */
	private $model;

	public function setUp(): void
	{
		$this->setUpDatabase();

		$this->ds = new NextrasDataSource($this->model->users->findAll(), 'id');
		$factory = new TestingDataGridFactory();
		$this->grid = $factory->createTestingDataGrid();
	}

	protected function setUpDatabase(): void
	{
		$args = Environment::loadData();

		$storage = new DevNullStorage();
		$cache = new Cache($storage);
		$connection = new Connection($args);

		$connection->query('DROP TABLE IF EXISTS `users`');
		$connection->query('CREATE TABLE `users` (
								`id` int(11) NOT NULL AUTO_INCREMENT,
								`name` varchar(50) COLLATE utf8_czech_ci NOT NULL,
								`age` int(11) NOT NULL,
								`address` varchar(50) NOT NULL,
								PRIMARY KEY (`id`)
							) ENGINE = InnoDB DEFAULT CHARSET = utf8 COLLATE = utf8_czech_ci;');

		$simpleModelFactory = new SimpleModelFactory($cache, [
			'users' => new UsersRepository(
				new UsersMapper($connection, new DbalMapperCoordinator($connection), $cache)
			),
		]);

		$this->model = $simpleModelFactory->create();

		$connection->query('INSERT INTO [users] %values[]', $this->data);
	}

	protected function getActualResultAsArray()
	{
		$result = [];

		/** @var User $row */
		foreach ($this->ds->getData() as $row) {
			$result[] = $row->toArray();
		}

		return $result;
	}

}

/**
 * User
 *
 * @property int $id {primary}
 * @property string $name
 * @property int $age
 * @property string $address
 */
class User extends Entity
{

}

class UsersMapper extends Mapper
{

}

class UsersRepository extends Repository
{

	public static function getEntityClassNames(): array
	{
		return [User::class];
	}

}

$test_case = new NextrasDataSourceTest();
$test_case->run();
