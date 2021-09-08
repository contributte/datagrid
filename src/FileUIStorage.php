<?php declare(strict_types = 1);

namespace Ublaboo\DataGrid;

use Kubomikita\Factory\CacheFactory;
use Nette\Caching\Cache;
use Nette\Caching\Storages\FileStorage;
use Nette\Security\User;
use Nette\Utils\FileSystem;


class FileUIStorage implements UIStorage {
	/** @var string */
	protected $storageName;
	/** @var string */
	protected $tempDir;
	/** @var User */
	protected $user;

	/** @var CacheFactory */
	protected $cacheFactory;

	private $buffer = [];

	public function __construct(User $user, CacheFactory $cacheFactory, $storageName = 'datagrid_ui', $tempDir = TEMP_DIR."/datagrid_ui")
	{
		$this->storageName = $storageName;
		$this->tempDir = $tempDir;
		$this->user = $user;
		$this->cacheFactory = $cacheFactory;


	}

	protected function cacheFactory() : Cache {
		if(!file_exists($this->tempDir)){
			FileSystem::createDir($this->tempDir);
		}
		$storage = new FileStorage($this->tempDir);
		return new Cache($storage,(string) $this->user->getId());
	}

	public function saveState(string $gridName, string $key, $value) : void
	{
		$this->buffer[$gridName] = $this->getCacheData($gridName);
		$this->buffer[$gridName][$key] = $value;
		$this->setCacheData($gridName);
	}
	public function getState(string $gridName, ?string $key = null, $defaultValue = null) {
		if($key !== null){
			$state = $this->getCacheData($gridName);
			if(isset($state[$key])){
				return $state[$key];
			}
			return $defaultValue;
		} else {
			return $this->getCacheData($gridName);
		}

	}
	public function flushState(string $gridName, string $key) :void
	{
		$this->buffer[$gridName] = $this->getCacheData($gridName);
		unset($this->buffer[$gridName][$key]);
		$this->setCacheData($gridName);
	}

	/**
	 * @param string $gridName
	 *
	 * @return array
	 */
	private function getCacheData(string $gridName) : array
	{
		if(($data = $this->cacheFactory()->load($this->getCacheKey($gridName))) !== null) {
			return unserialize($data);
		}
		return [];
	}

	/**
	 * @param string $gridName
	 */
	private function setCacheData(string $gridName) : void
	{
		$this->cacheFactory()->save($this->getCacheKey($gridName), serialize($this->buffer[$gridName]));
	}

	/**
	 * @param string $gridName
	 *
	 * @return string
	 */
	private function getCacheKey(string $gridName) : string
	{
		return $gridName;
	}
}