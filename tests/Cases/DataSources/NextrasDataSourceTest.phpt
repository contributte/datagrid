<?php declare(strict_types = 1);

namespace Contributte\Datagrid\Tests\Cases\DataSources;

use Contributte\Datagrid\DataSource\NextrasDataSource;
use Contributte\Datagrid\Filter\FilterText;
use Contributte\Datagrid\Tests\Files\TestingDatagridFactory;
use Nette\Caching\Cache;
use Nette\Caching\Storages\DevNullStorage;
use Nette\Utils\Arrays;
use Nextras\Dbal\Connection;
use Nextras\Orm\Collection\Expression\LikeExpression;
use Nextras\Orm\Entity\Entity;
use Nextras\Orm\Mapper\Dbal\DbalMapperCoordinator;
use Nextras\Orm\Mapper\Mapper;
use Nextras\Orm\Model\Model;
use Nextras\Orm\Model\SimpleModelFactory;
use Nextras\Orm\Repository\Repository;
use Tester\Assert;
use Tester\Environment;

require __DIR__ . '/BaseDataSourceTest.phpt';

if (!extension_loaded('mysqli')) {
	Environment::skip('Test requires MySQLi extension to be loaded.');
}

/**
 * @dataProvider nextrasDatasource.ini
 */
final class NextrasDataSourceTest extends BaseDataSourceTest
{

	private Model $model;

	public function testFilterOnJoinedTable(): void
	{
		// skip this test for v3.1
		if (!class_exists(LikeExpression::class)) {
			return;
		}

		$this->ds = new NextrasDataSource($this->model->books->findAll(), 'id');

		$filter = new FilterText($this->grid, 'a', 'b', ['author.name']);
		$filter->setValue('John Red');

		$this->ds->filter([$filter]);
		Assert::same(2, $this->ds->getCount());
	}

	protected function setUp(): void
	{
		$this->setUpDatabase();

		$this->ds = new NextrasDataSource($this->model->users->findAll(), 'id');
		$factory = new TestingDatagridFactory();
		$this->grid = $factory->createTestingDatagrid();
	}

	protected function setUpDatabase(): void
	{
		$args = Environment::loadData();

		$storage = new DevNullStorage();
		$cache = new Cache($storage);
		$connection = new Connection($args);

		if (!$connection->ping()) {
			Environment::skip('MySQL is not running');
		}

		$connection->query('DROP TABLE IF EXISTS `books`');
		$connection->query('DROP TABLE IF EXISTS `users`');
		$connection->query('CREATE TABLE `users` (
								`id` int(11) NOT NULL AUTO_INCREMENT,
								`name` varchar(50) COLLATE utf8_czech_ci NOT NULL,
								`age` int(11) NOT NULL,
								`address` varchar(50) NOT NULL,
								PRIMARY KEY (`id`)
							) ENGINE = InnoDB DEFAULT CHARSET = utf8 COLLATE = utf8_czech_ci;');
		$connection->query('CREATE TABLE IF NOT EXISTS `books` (
  								`id` int(11) NOT NULL AUTO_INCREMENT,
  								`author_id` int(11) NOT NULL,
  								PRIMARY KEY (`id`),
  								KEY `author_id` (`author_id`),
  								CONSTRAINT `author_id` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`)
							) ENGINE = InnoDB DEFAULT CHARSET = utf8 COLLATE = utf8_czech_ci;');

		$simpleModelFactory = new SimpleModelFactory($cache, [
			'users' => new UsersRepository(
				new UsersMapper($connection, new DbalMapperCoordinator($connection), $cache)
			),
			'books' => new BooksRepository(
				new BooksMapper($connection, new DbalMapperCoordinator($connection), $cache)
			),
		]);

		$this->model = $simpleModelFactory->create();

		$connection->query('INSERT INTO [users] %values[]', $this->data);
		$connection->query('INSERT INTO [books] %values[]', Arrays::map($this->data, fn (array $data): array => ['id' => $data['id'], 'author_id' => $data['id']]));
	}

	protected function getActualResultAsArray(): array
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

/**
 * Book
 *
 * @property int $id {primary}
 * @property User $author {m:1 User, oneSided=true}
 */
class Book extends Entity
{

}

class BooksMapper extends Mapper
{

}

class BooksRepository extends Repository
{

	public static function getEntityClassNames(): array
	{
		return [Book::class];
	}

}

$test_case = new NextrasDataSourceTest();
$test_case->run();
