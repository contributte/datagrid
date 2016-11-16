<?php

namespace Ublaboo\DataGrid\Tests\Cases\DataSources;

use Nextras\Orm\Relationships\OneHasMany;
use Tester\Environment;
use Tester\TestCase;
use Tester\Assert;
use Ublaboo\DataGrid\DataSource\NextrasDataSource;

require __DIR__ . '/BaseDataSourceTest.phpt';

if (!extension_loaded('mysqli')) {
    \Tester\Environment::skip('Test requires MySQLi extension to be loaded.');
}

/**
 * @dataProvider nextrasDatasource.ini
 */
final class NextrasDataSourceTest extends BaseDataSourceTest {

	/** @var \Nextras\Orm\Model\Model */
	private $model;

	public function setUp() {
		$this->setUpDatabase();

		$this->ds = new NextrasDataSource($this->model->users->findAll(), 'id');
		$factory = new \Ublaboo\DataGrid\Tests\Files\XTestingDataGridFactory;
		$this->grid = $factory->createXTestingDataGrid();
	}

	protected function setUpDatabase() {
		$args = \Tester\Environment::loadData();

		$storage = new \Nette\Caching\Storages\DevNullStorage();
		$cache = new \Nette\Caching\Cache($storage);
		$connection = new \Nextras\Dbal\Connection($args);

		$connection->query("DROP TABLE IF EXISTS `users`");
		$connection->query("CREATE TABLE `users` (
								`id` int(11) NOT NULL AUTO_INCREMENT,
								`name` varchar(50) COLLATE utf8_czech_ci NOT NULL,
								`age` int(11) NOT NULL,
								`address` varchar(50) NOT NULL,
								PRIMARY KEY (`id`)
							) ENGINE = InnoDB DEFAULT CHARSET = utf8 COLLATE = utf8_czech_ci;");

		$simpleModelFactory = new \Nextras\Orm\Model\SimpleModelFactory($cache, [
			'users' => new UsersRepository(new UsersMapper($connection, $cache)),
		]);

		$this->model = $simpleModelFactory->create();

		$connection->query('INSERT INTO [users] %values[]', $this->data);
	}

	protected function getActualResultAsArray() {
		$result = [];
		foreach ($this->ds->getData() as $row) { /* @var $row User */
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
class User extends \Nextras\Orm\Entity\Entity {

}

class UsersMapper extends \Nextras\Orm\Mapper\Mapper {

}

class UsersRepository extends \Nextras\Orm\Repository\Repository {

	public static function getEntityClassNames() {
		return [User::class];
	}

}

$test_case = new NextrasDataSourceTest;
$test_case->run();
