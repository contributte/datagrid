<?php

namespace Ublaboo\DataGrid\Tests\Cases\DataSources;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Setup;
use Tester\Assert;
use Ublaboo;
use Ublaboo\DataGrid\DataSource\DoctrineDataSource;
use Ublaboo\DataGrid\Tests\Cases\DataSources\Doctrine\City;
use Ublaboo\DataGrid\Tests\Cases\DataSources\Doctrine\User;

require __DIR__ . '/BaseDataSourceTest.phpt';

final class DoctrineDataSourceTest extends BaseDataSourceTest
{
	/**
	 * @var \Doctrine\DBAL\Connection
	 */
	private $db;

    /**
     * @var EntityManager
     */
    private $entityManager;

	public function setUp()
	{
		$this->setUpDatabase();

        $tmpDir = __DIR__ . '/../../tmp';
        $config = Setup::createAnnotationMetadataConfiguration([$tmpDir], false, $tmpDir);
        $config->setAutoGenerateProxyClasses(true);
        $this->entityManager = EntityManager::create($this->db, $config);

        $queryBuilder = $this->entityManager->getRepository(User::class)->createQueryBuilder('u');

        $this->ds = new DoctrineDataSource($queryBuilder, 'id');
        $factory = new Ublaboo\DataGrid\Tests\Files\XTestingDataGridFactory;
        $this->grid = $factory->createXTestingDataGrid();
    }

	protected function setUpDatabase()
	{
		$config = new Configuration();

		$this->db = DriverManager::getConnection(['url' => 'sqlite:///:memory:'], $config);

        $this->db->executeQuery(file_get_contents(__DIR__ . '/config/schema_users.sql'));
        $this->db->executeQuery(file_get_contents(__DIR__ . '/config/schema_cities.sql'));

        foreach($this->data['users'] as $row) {
            $this->db->insert('users', $row);
        }

        foreach($this->data['cities'] as $row) {
            $this->db->insert('cities', $row);
        }
    }


    protected function getActualResultAsArray()
    {
        $result = parent::getActualResultAsArray();

        foreach ($result as &$row) {
            // user entity - modify relation with city
            if (isset($row['city'])) {
                $row['city'] = $row['city']['id'];
            }

            // TODO
            // city entity - modify datetime and remove users relation
            if (isset($row['users'])) {
                unset($row['users']);
                $row['created'] = substr($row['created']['date'], 0, 22);
            }
        }

        return $result;
    }
}

$test_case = new DoctrineDataSourceTest();
$test_case->run();
