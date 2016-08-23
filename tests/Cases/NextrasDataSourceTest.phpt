<?php

namespace Ublaboo\DataGrid\Tests\Cases;

use Tester\TestCase,
    Tester\Assert,
    Ublaboo\DataGrid\DataGrid,
    Ublaboo\DataGrid\DataSource\NextrasDataSource,
    Ublaboo\DataGrid\Utils\Sorting,
    Ublaboo\DataGrid\Filter\FilterRange,
    Ublaboo\DataGrid\Filter\FilterDateRange,
    Ublaboo\DataGrid\Filter\FilterDate,
    Ublaboo\DataGrid\Filter\FilterSelect;

require __DIR__ . '/../bootstrap.php';
require __DIR__ . '/../Files/XTestingDataGridFactory.php';
require __DIR__ . '/../Files/TestingNextrasOrm.php';

/**
 * @dataProvider ../Files/nextrasDatasource.ini
 */
final class NextrasDataSourceTest extends TestCase {

    /** @var \Nextras\Orm\Model\Model */
    private $model;

    /** @var DataGrid */
    private $grid;

    /** @var NextrasDataSource */
    private $ds;

    /** @var \Nextras\Orm\Mapper\Dbal\DbalCollection */
    private $collection;

    public function __construct() {
        $args = \Tester\Environment::loadData();

        $storage = new \Nette\Caching\Storages\DevNullStorage();
        $cache = new \Nette\Caching\Cache($storage);
        $connection = new \Nextras\Dbal\Connection($args);

        $connection->query('SET foreign_key_checks = 0');
        $connection->query("DROP TABLE IF EXISTS `roles`");
        $connection->query("DROP TABLE IF EXISTS `users`");
        $connection->query("CREATE TABLE `roles` (
                                `id` int(11) NOT NULL AUTO_INCREMENT,
                                `inserted` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                `name` varchar(50) COLLATE utf8_czech_ci NOT NULL,
                                PRIMARY KEY (`id`)
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;");
        $connection->query("INSERT INTO `roles` (`id`, `inserted`, `name`) VALUES
                                (1,	'2016-08-23 00:00:00',	'admin'),
                                (2,	'2016-08-22 00:00:00',	'user'),
                                (3,	'2016-08-23 00:00:00',	'editor');");
        $connection->query("CREATE TABLE `users` (
                                `id` int(11) NOT NULL AUTO_INCREMENT,
                                `username` varchar(100) COLLATE utf8_czech_ci NOT NULL,
                                `name` varchar(50) COLLATE utf8_czech_ci NOT NULL,
                                `age` int(11) NOT NULL,
                                `role_id` int(11) NOT NULL,
                                PRIMARY KEY (`id`),
                                KEY `role_id` (`role_id`),
                                CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
                            ) ENGINE = InnoDB DEFAULT CHARSET = utf8 COLLATE = utf8_czech_ci;");
        $connection->query("INSERT INTO `users` (`id`, `username`, `name`, `age`, `role_id`) VALUES
                                (1, 'admin', 'Joe', 30, 1),
                                (2, 'User', 'Clark', 30, 2),
                                (3, 'User', 'John', 20, 3);");
        $connection->query('SET foreign_key_checks = 1');


        $simpleModelFactory = new \Nextras\Orm\Model\SimpleModelFactory($cache, [
            'roles' => new \Ublaboo\DataGrid\Tests\Files\Orm\RolesRepository(new \Ublaboo\DataGrid\Tests\Files\Orm\RolesMapper($connection, $cache)),
            'users' => new \Ublaboo\DataGrid\Tests\Files\Orm\UsersRepository(new \Ublaboo\DataGrid\Tests\Files\Orm\UsersMapper($connection, $cache)),
        ]);

        $this->model = $simpleModelFactory->create();

        $factory = new \Ublaboo\DataGrid\Tests\Files\XTestingDataGridFactory;
        $this->grid = $factory->createXTestingDataGrid();
    }

    public function setUp() {
        $this->collection = $this->model->users->findAll();
        $this->ds = new NextrasDataSource($this->collection, $this->grid->getPrimaryKey());
        $factory = new \Ublaboo\DataGrid\Tests\Files\XTestingDataGridFactory;
        $this->grid = $factory->createXTestingDataGrid();
    }

    public function testGetCount() {
        Assert::same($this->collection->count(), $this->ds->getCount());
    }

    public function testGetData() {
        Assert::same($this->collection->fetchAll(), $this->ds->getData());
    }

    public function testFitlerOne() {
        $filter = ['id' => 2];

        $this->ds->filterOne($filter);

        Assert::same($this->collection->findBy($filter)->fetchAll(), array_values($this->ds->getData()));
    }

    public function testLimit() {
        $this->ds->limit(2, 2);

        Assert::same($this->collection->limitBy(2, 2)->fetchAll(), $this->ds->getData());
    }

    public function testSort() {
        $sort = ['name' => 'DESC'];

        $this->ds->sort(new Sorting($sort));

        Assert::same($this->collection->orderBy($sort)->fetchAll(), $this->ds->getData());
    }

    public function testFilterRange() {
        $test = [2, 3];

        $filter = new FilterRange($this->grid, 'a', 'b', 'role.id', '-');
        $filter->setValue([
            'from' => $test[0],
            'to' => $test[1]]);

        $result = $this->collection->findBy([
                    'this->role->id>=' => $test[0],
                    'this->role->id<=' => $test[1]
                ])->fetchAll();

        $this->ds->filter([$filter]);
        Assert::same($result, $this->ds->getData());
    }

    public function testFilterDateRange() {
        $test = [\DateTime::createFromFormat('Y-m-d H:i:s', '2016-08-22 00:00:00') , \DateTime::createFromFormat('Y-m-d H:i:s', '2016-08-23 23:59:59')];

        $filter = new FilterDateRange($this->grid, 'a', 'b', 'role.inserted', '-');
        $filter->setValue([
            'from' => $test[0]->format('d. m. Y'),
            'to' => $test[1]->format('d. m. Y')]);

        $result = $this->collection->findBy([
                    'this->role->inserted>=' => $test[0],
                    'this->role->inserted<=' => $test[1]
                ])->fetchAll();

        $this->ds->filter([$filter]);
        Assert::same($result, $this->ds->getData());
    }

    public function testFilterDate() {
        $test = \DateTime::createFromFormat('Y-m-d H:i:s', '2016-08-22 00:00:00');

        $filter = new FilterDate($this->grid, 'a', 'b', 'role.inserted');
        $filter->setValue($test->format('d. m. Y'));

        $result = $this->collection->findBy([
                    'this->role->inserted' => $test
                ])->fetchAll();

        $this->ds->filter([$filter]);
        Assert::same($result, $this->ds->getData());
    }
    
    public function testFilterSelect() {
        $test = 2;

        $options=$this->model->roles->findAll()->fetchAll('id', 'name');
        
        $filter = new FilterSelect($this->grid, 'a', 'b', $options,'role.id');
        $filter->setValue($test);

        $result = $this->collection->findBy([
                    'this->role->id' => $test
                ])->fetchAll();

        $this->ds->filter([$filter]);
        Assert::same($result, $this->ds->getData());
    }

}

$test_case = new NextrasDataSourceTest;
$test_case->run();
