<?php
namespace EntityManager;

use Pimple\Container;
use Zend\Config\Config;
use Zend\Db\Adapter\Adapter as DbAdapter;
use EntityManager\Entity\AbstractEntity;
use EntityManager\Collection\Collection;
use EntityManager\Mapper\Conditions\Conditions;
use EntityManager\IdentityMap;
use EntityManager\UnitOfWork;
use EntityManager\Entity\Factory as EntityFactory;
use EntityManager\Entity\Builder as EntityBuilder;
use EntityManager\Collection\Factory as CollectionFactory;
use EntityManager\Mapper\Factory as MapperFactory;

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
        $container['config'] = new Config($config);

        $container['db'] = function ($container) {

            if (!isset($container['config']->db)) {
                return null;
            }

            return new DbAdapter($container['config']->db->toArray());
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
     * @param string $type
     * @return AbstractEntity
     */
    public function createEntity($type)
    {
        return $this->container['entityFactory']->create($type);
    }

    /**
     * @return Conditions
     */
    public function createConditions()
    {
        return new Conditions();
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
     * @param Conditions $conditions
     * @return AbstractEntity
     */
    public function findOne($type, Conditions $conditions = null)
    {
        $mapper = $this->container['mapperFactory']->createForType($type);
        return $mapper->findOne($type, $conditions);
    }

    /**
     * @param string $type
     * @param Conditions $conditions
     * @return Collection
     */
    public function findAll($type, Conditions $conditions = null)
    {
        $mapper = $this->container['mapperFactory']->createForType($type);
        return $mapper->findAll($type, $conditions);
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
     * @todo do we need this anymore?
     * @param AbstractEntity $entity
     * @return EntityManager
     */
    public function dirty(AbstractEntity $entity)
    {
        $this->container['unitOfWork']->dirty($entity);
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