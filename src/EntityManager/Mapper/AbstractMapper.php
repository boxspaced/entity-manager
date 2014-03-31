<?php

abstract class EntityManager_Mapper_AbstractMapper
{

    /**
     * @var EntityManager_IdentityMap
     */
    private $identityMap;

    /**
     * @var EntityManager_Builder_AbstractBuilder
     */
    private $builder;

    /**
     * @return string
     */
    abstract protected function getEntityClassName();

    /**
     * @param int $id
     * @return array
     */
    abstract protected function doFind($id);

    /**
     * @param EntityManager_Mapper_Conditions_Conditions $conditions
     * @return array
     */
    abstract protected function doFindOne(EntityManager_Mapper_Conditions_Conditions $conditions = null);

    /**
     * @param EntityManager_Mapper_Conditions_Conditions $conditions
     * @return array
     */
    abstract protected function doFindAll(EntityManager_Mapper_Conditions_Conditions $conditions = null);

    /**
     * @param EntityManager_EntityInterface $entity
     * @return EntityManager_Mapper_AbstractMapper
     */
    abstract protected function doInsert(EntityManager_EntityInterface $entity);

    /**
     * @param EntityManager_EntityInterface $entity
     * @return EntityManager_Mapper_AbstractMapper
     */
    abstract protected function doUpdate(EntityManager_EntityInterface $entity);

    /**
     * @param EntityManager_EntityInterface $entity
     * @return EntityManager_Mapper_AbstractMapper
     */
    abstract protected function doDelete(EntityManager_EntityInterface $entity);

    /**
     * @param EntityManager_IdentityMap $identityMap
     * @param EntityManager_Builder_AbstractBuilder $builder
     */
    public function __construct(
        EntityManager_IdentityMap $identityMap,
        EntityManager_Builder_AbstractBuilder $builder)
    {
        $this->identityMap = $identityMap;
        $this->builder = $builder;
    }

    /**
     * @param int $id
     * @return EntityManager_EntityInterface|bool
     */
    public function find($id)
    {
        $existing = $this->getFromIdentityMap($id);
        if ($existing) {
            return $existing;
        }
        $row = $this->doFind($id);
        if (!$row) {
            return false;
        }
        return $this->builder->build($row);
    }

    /**
     * @param EntityManager_Mapper_Conditions_Conditions $conditions
     * @return EntityManager_EntityInterface|bool
     */
    public function findOne(EntityManager_Mapper_Conditions_Conditions $conditions = null)
    {
        $row = $this->doFindOne($conditions);
        if (!$row) {
            return false;
        }
        return $this->builder->build($row);
    }

    /**
     * @param EntityManager_Mapper_Conditions_Conditions $conditions
     * @return EntityManager_Collection_AbstractCollection
     */
    public function findAll(EntityManager_Mapper_Conditions_Conditions $conditions = null)
    {
        $rowsetCallback = function() use ($conditions) {
            return $this->doFindAll($conditions);
        };
        $rowsetCallback->bindTo($this);
        return $this->builder->createCollection($rowsetCallback);
    }

    /**
     * @param EntityManager_EntityInterface $entity
     * @return EntityManager_Mapper_AbstractMapper
     */
    public function insert(EntityManager_EntityInterface $entity)
    {
        $this->entityTypeCheck($entity);
        $this->doInsert($entity);
        $this->addToIdentityMap($entity);
        return $this;
    }

    /**
     * @param EntityManager_EntityInterface $entity
     * @return EntityManager_Mapper_AbstractMapper
     */
    public function update(EntityManager_EntityInterface $entity)
    {
        $this->entityTypeCheck($entity);
        $this->doUpdate($entity);
        return $this;
    }

    /**
     * @param EntityManager_EntityInterface $entity
     * @return EntityManager_Mapper_AbstractMapper
     */
    public function delete(EntityManager_EntityInterface $entity)
    {
        $this->entityTypeCheck($entity);
        $this->doDelete($entity);
        return $this;
    }

    /**
     *
     * @param EntityManager_EntityInterface $entity
     * @return int|null
     */
    protected function returnEntityIdOrNull(EntityManager_EntityInterface $entity = null)
    {
        return ($entity) ? $entity->getId() : null;
    }

    /**
     * @param EntityManager_EntityInterface $entity
     * @return EntityManager_Mapper_AbstractMapper
     * @throws EntityManager_Mapper_Exception
     */
    protected function entityTypeCheck(EntityManager_EntityInterface $entity)
    {
        if (!is_a($entity, $this->getEntityClassName())) {
            throw new EntityManager_Mapper_Exception('Entities passed to this mapper must be of type: '
                    . $this->getEntityClassName() . ' (' . get_class($entity) .') provided');
        }
        return $this;
    }

    /**
     * @param int $id
     * @return EntityManager_EntityInterface|bool
     */
    private function getFromIdentityMap($id)
    {
        $entityClassName = $this->getEntityClassName();
        return $this->identityMap->exists($entityClassName, $id);
    }

    /**
     * @param EntityManager_EntityInterface $entity
     * @return EntityManager_Mapper_AbstractMapper
     */
    private function addToIdentityMap(EntityManager_EntityInterface $entity)
    {
        $this->identityMap->add($entity);
        return $this;
    }

}
