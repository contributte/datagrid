<?php

namespace Ublaboo\DataGrid\Tests\Cases\DataSources;

use Ublaboo\DataGrid\DataSource\NextrasDataSource;
use Ublaboo\DataGrid\Tests\Cases\DataSources\Nextras\CitiesMapper;
use Ublaboo\DataGrid\Tests\Cases\DataSources\Nextras\CitiesRepository;
use Ublaboo\DataGrid\Tests\Cases\DataSources\Nextras\PetsMapper;
use Ublaboo\DataGrid\Tests\Cases\DataSources\Nextras\PetsRepository;
use Ublaboo\DataGrid\Tests\Cases\DataSources\Nextras\User;
use Ublaboo\DataGrid\Tests\Cases\DataSources\Nextras\UsersMapper;
use Ublaboo\DataGrid\Tests\Cases\DataSources\Nextras\UsersRepository;

require __DIR__ . '/BaseDataSourceTest.phpt';

if (!extension_loaded('mysqli')) {
    \Tester\Environment::skip('Test requires MySQLi extension to be loaded.');
}

/**
 * @dataProvider config/nextrasDatasource.ini
 */
final class NextrasDataSourceTest extends BaseDataSourceTest
{

	/** @var \Nextras\Orm\Model\Model */
	private $model;

    public function setUp()
    {
        $this->setUpDatabase();
		$this->ds = new NextrasDataSource($this->model->users->findAll(), 'id');
		$factory = new \Ublaboo\DataGrid\Tests\Files\XTestingDataGridFactory;
		$this->grid = $factory->createXTestingDataGrid();
	}

    protected function setUpDatabase()
    {
        $args = \Tester\Environment::loadData();
		$storage = new \Nette\Caching\Storages\DevNullStorage();
		$cache = new \Nette\Caching\Cache($storage);
		$connection = new \Nextras\Dbal\Connection($args);

        $connection->query("DROP TABLE IF EXISTS `users`;");
        $connection->query("DROP TABLE IF EXISTS `cities`;");

        $connection->query("CREATE TABLE `cities` (
                                `id` int(11) NOT NULL AUTO_INCREMENT,
                                `name` varchar(50) COLLATE utf8_czech_ci NOT NULL,
                                `created` datetime NOT NULL,
                                PRIMARY KEY (`id`)
                            ) ENGINE = InnoDB DEFAULT CHARSET = utf8 COLLATE = utf8_czech_ci;");

        $connection->query("CREATE TABLE `users` (
                                `id` int(11) NOT NULL AUTO_INCREMENT,
                                `name` varchar(50) COLLATE utf8_czech_ci NOT NULL,
                                `age` int(11) NOT NULL,
                                `address` varchar(50) NOT NULL,
                                `city` int(11) NOT NULL,
                                KEY `city` (`city`),
                                CONSTRAINT `cities_ibfk_1` FOREIGN KEY (`city`) REFERENCES `cities` (`id`),
                                PRIMARY KEY (`id`)
                            ) ENGINE = InnoDB DEFAULT CHARSET = utf8 COLLATE = utf8_czech_ci;");

        $simpleModelFactory = new \Nextras\Orm\Model\SimpleModelFactory($cache, [
            'users' => new UsersRepository(new UsersMapper($connection, $cache)),
            'cities' => new CitiesRepository(new CitiesMapper($connection, $cache))
        ]);

		$this->model = $simpleModelFactory->create();

        $connection->query('INSERT INTO [cities] %values[]', $this->data['cities']);
        $connection->query('INSERT INTO [users] %values[]', $this->data['users']);
    }

    protected function getActualResultAsArray()
    {
        $result = [];
        foreach ($this->ds->getData() as $row) { /* @var $row User */
            $tmp = $row->toArray();
            // TODO replace this punk
            $tmp['city'] = $row->city->id;
            $result[] = $tmp;
        }
        return $result;
    }
}

$test_case = new NextrasDataSourceTest;
$test_case->run();
