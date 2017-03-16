<?php
namespace Boxspaced\EntityManager;

use Pimple\Container;
use Zend\Db\Adapter\Adapter as Database;
use Boxspaced\EntityManager\Entity\AbstractEntity;
use Boxspaced\EntityManager\Collection\Collection;
use Boxspaced\EntityManager\Mapper\Query;
use Boxspaced\EntityManager\IdentityMap;
use Boxspaced\EntityManager\UnitOfWork;
use Boxspaced\EntityManager\Entity\EntityFactory;
use Boxspaced\EntityManager\Entity\EntityBuilder;
use Boxspaced\EntityManager\Collection\CollectionFactory;
use Boxspaced\EntityManager\Mapper\MapperFactory;
use Boxspaced\EntityManager\Mapper\MapperStrategyInterface;
use MongoDB\Client as MongoDb;

class EntityManager
{

    /**
     * @var Container
     */
    protected $container;

    /**
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $container = new Container();
        $container['config'] = $config;

        $container['db'] = function ($container) {

            if (!isset($container['config']['db'])) {
                return null;
            }

            return new Database($container['config']['db']);
        };

        $container['mongoDb'] = function ($container) {

            if (!isset($container['config']['mongo_db'])) {
                return null;
            }

            if (!class_exists(MongoDb::class)) {
                throw new Exception\RuntimeException('The MongoDB driver library is not installed');
            }

            $config = $container['config']['mongo_db'];

            return new MongoDb(
                isset($config['uri']) ? $config['uri'] : null,
                isset($config['uri_options']) ? $config['uri_options'] : [],
                isset($config['driver_options']) ? $config['driver_options'] : []
            );
        };

        $container['identityMap'] = function () {
            return new IdentityMap();
        };

        $container['unitOfWork'] = function ($container) {

            $unitOfWork = new UnitOfWork(
                $container['mapperFactory']
            );

            if (null !== $container['db']) {
                $unitOfWork->setDb($container['db']);
            }

            return $unitOfWork;
        };

        $container['entityFactory'] = function ($container) {
            return new EntityFactory($container);
        };

        $container['entityBuilder'] = function ($container) {
            return new EntityBuilder(
                $container['identityMap'],
                $container['unitOfWork'],
                $container['entityFactory'],
                $container['mapperFactory'],
                $container['config']
            );
        };

        $container['collectionFactory'] = function ($container) {
            return new CollectionFactory($container);
        };

        $container['mapperFactory'] = function ($container) {
            return new MapperFactory($container);
        };

        $this->container = $container;
    }

    /**
     * @param MapperStrategyInterface $mapperStrategy
     */
    public function addMapperStrategy(MapperStrategyInterface $mapperStrategy)
    {
        $this->container['mapperFactory']->addMapperStrategy($mapperStrategy);
    }

    /**
     * @return Database
     */
    public function getDb()
    {
        return $this->container['db'];
    }

    /**
     * @return MongoDb
     */
    public function getMongoDb()
    {
        return $this->container['mongoDb'];
    }

    /**
     * @param string $type
     * @return AbstractEntity
     */
    public function createEntity($type)
    {
        return $this->container['entityFactory']->create($type);
    }

    /**
     * @return Query
     */
    public function createQuery()
    {
        return new Query();
    }

    /**
     * @param string $type
     * @param int $id
     * @return AbstractEntity
     */
    public function find($type, $id)
    {
        $mapper = $this->container['mapperFactory']->createForType($type);
        return $mapper->find($type, $id);
    }

    /**
     * @param string $type
     * @param Query $query
     * @return AbstractEntity
     */
    public function findOne($type, Query $query = null)
    {
        $mapper = $this->container['mapperFactory']->createForType($type);
        return $mapper->findOne($type, $query);
    }

    /**
     * @param string $type
     * @param Query $query
     * @return Collection
     */
    public function findAll($type, Query $query = null)
    {
        $mapper = $this->container['mapperFactory']->createForType($type);
        return $mapper->findAll($type, $query);
    }

    /**
     * @param AbstractEntity $entity
     * @return EntityManager
     */
    public function persist(AbstractEntity $entity)
    {
        $this->container['unitOfWork']->persist($entity);
        return $this;
    }

    /**
     * @param AbstractEntity $entity
     * @return EntityManager
     */
    public function delete(AbstractEntity $entity)
    {
        $this->container['unitOfWork']->delete($entity);
        return $this;
    }

    /**
     * @return EntityManager
     */
    public function flush()
    {
        $this->container['unitOfWork']->flush();
        return $this;
    }

}
