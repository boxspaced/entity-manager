<?php
namespace Boxspaced\EntityManager\Mapper;

use Boxspaced\EntityManager\IdentityMap;
use Boxspaced\EntityManager\Entity\EntityBuilder;
use Boxspaced\EntityManager\Collection\CollectionFactory;
use Boxspaced\EntityManager\Entity\AbstractEntity;
use Boxspaced\EntityManager\Collection\Collection;

class Mapper
{

    /**
     * @var IdentityMap
     */
    protected $identityMap;

    /**
     * @var EntityBuilder
     */
    protected $entityBuilder;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var MapperStrategyInterface
     */
    protected $strategy;

    /**
     * @param IdentityMap $identityMap
     * @param EntityBuilder $entityBuilder
     * @param CollectionFactory $collectionFactory
     * @param MapperStrategyInterface $strategy
     */
    public function __construct(
        IdentityMap $identityMap,
        EntityBuilder $entityBuilder,
        CollectionFactory $collectionFactory,
        MapperStrategyInterface $strategy
    )
    {
        $this->identityMap = $identityMap;
        $this->entityBuilder = $entityBuilder;
        $this->collectionFactory = $collectionFactory;
        $this->strategy = $strategy;
    }

    /**
     * @param string $type
     * @param int $id
     * @return AbstractEntity
     */
    public function find($type, $id)
    {
        $existing = $this->identityMap->exists($type, $id);

        if ($existing) {
            return $existing;
        }

        $data = $this->strategy->find($type, $id);

        if (!$data) {
            return null;
        }

        return $this->entityBuilder->build($type, $data);
    }

    /**
     * @param string $type
     * @param Query $query
     * @return AbstractEntity
     */
    public function findOne($type, Query $query = null)
    {
        $data = $this->strategy->findOne($type, $query);

        if (!$data) {
            return null;
        }

        return $this->entityBuilder->build($type, $data);
    }

    /**
     * @param string $type
     * @param Query $query
     * @return Collection
     */
    public function findAll($type, Query $query = null)
    {
        $dataset = function() use ($type, $query) {
            return $this->strategy->findAll($type, $query);
        };
        $dataset->bindTo($this);

        return $this->collectionFactory->create($type, $dataset);
    }

    /**
     * @param AbstractEntity $entity
     * @return Mapper
     */
    public function insert(AbstractEntity $entity)
    {
        $this->strategy->insert($entity);
        $this->identityMap->add($entity);
        return $this;
    }

    /**
     * @param AbstractEntity $entity
     * @return Mapper
     */
    public function update(AbstractEntity $entity)
    {
        $this->strategy->update($entity);
        return $this;
    }

    /**
     * @param AbstractEntity $entity
     * @return Mapper
     */
    public function delete(AbstractEntity $entity)
    {
        $this->strategy->delete($entity);
        return $this;
    }

}
