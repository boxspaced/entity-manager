<?php

abstract class EntityManager_Mapper_AbstractMapper
{

    /**
     * @var EntityManager_IdentityMap
     */
    private $_identityMap;

    /**
     * @var EntityManager_Builder_AbstractBuilder
     */
    private $_builder;

    /**
     * @return string
     */
    abstract protected function _getEntityClassName();

    /**
     * @param int $id
     * @return array
     */
    abstract protected function _find($id);

    /**
     * @param EntityManager_Mapper_Conditions_Conditions $conditions
     * @return array
     */
    abstract protected function _findOne(EntityManager_Mapper_Conditions_Conditions $conditions = null);

    /**
     * @param EntityManager_Mapper_Conditions_Conditions $conditions
     * @return array
     */
    abstract protected function _findAll(EntityManager_Mapper_Conditions_Conditions $conditions = null);

    /**
     * @param EntityManager_EntityInterface $entity
     * @return void
     */
    abstract protected function _insert(EntityManager_EntityInterface $entity);

    /**
     * @param EntityManager_EntityInterface $entity
     * @return void
     */
    abstract protected function _update(EntityManager_EntityInterface $entity);

    /**
     * @param EntityManager_EntityInterface $entity
     * @return void
     */
    abstract protected function _delete(EntityManager_EntityInterface $entity);

    /**
     * @param EntityManager_IdentityMap $identityMap
     * @param EntityManager_Builder_AbstractBuilder $builder
     */
    public function __construct(
        EntityManager_IdentityMap $identityMap,
        EntityManager_Builder_AbstractBuilder $builder)
    {
        $this->_identityMap = $identityMap;
        $this->_builder = $builder;
    }

    /**
     * @param int $id
     * @return EntityManager_EntityInterface|bool
     */
    public function find($id)
    {
        $existing = $this->_getFromIdentityMap($id);
        if ($existing) {
            return $existing;
        }
        $row = $this->_find($id);
        if (!$row) {
            return false;
        }
        return $this->_builder->build($row);
    }

    /**
     * @param EntityManager_Mapper_Conditions_Conditions $conditions
     * @return EntityManager_EntityInterface|bool
     */
    public function findOne(EntityManager_Mapper_Conditions_Conditions $conditions = null)
    {
        $row = $this->_findOne($conditions);
        if (!$row) {
            return false;
        }
        return $this->_builder->build($row);
    }

    /**
     * @param EntityManager_Mapper_Conditions_Conditions $conditions
     * @return EntityManager_Collection_AbstractCollection
     */
    public function findAll(EntityManager_Mapper_Conditions_Conditions $conditions = null)
    {
        $rowsetCallback = function() use ($conditions) {
            return $this->_findAll($conditions);
        };
        $rowsetCallback->bindTo($this);
        return $this->_builder->createCollection($rowsetCallback);
    }

    /**
     * @param EntityManager_EntityInterface $entity
     * @return EntityManager_Mapper_AbstractMapper
     */
    public function insert(EntityManager_EntityInterface $entity)
    {
        $this->_entityTypeCheck($entity);
        $this->_insert($entity);
        $this->_addToIdentityMap($entity);
        return $this;
    }

    /**
     * @param EntityManager_EntityInterface $entity
     * @return EntityManager_Mapper_AbstractMapper
     */
    public function update(EntityManager_EntityInterface $entity)
    {
        $this->_entityTypeCheck($entity);
        $this->_update($entity);
        return $this;
    }

    /**
     * @param EntityManager_EntityInterface $entity
     * @return EntityManager_Mapper_AbstractMapper
     */
    public function delete(EntityManager_EntityInterface $entity)
    {
        $this->_entityTypeCheck($entity);
        $this->_delete($entity);
        return $this;
    }

    /**
     *
     * @param EntityManager_EntityInterface $entity
     * @return int|null
     */
    protected function _returnEntityIdOrNull(EntityManager_EntityInterface $entity = null)
    {
        return ($entity) ? $entity->getId() : null;
    }

    /**
     * @param EntityManager_EntityInterface $entity
     * @return void
     * @throws EntityManager_Mapper_Exception
     */
    protected function _entityTypeCheck(EntityManager_EntityInterface $entity)
    {
        if (!is_a($entity, $this->_getEntityClassName())) {
            throw new EntityManager_Mapper_Exception('Entities passed to this mapper must be of type: '
                    . $this->_getEntityClassName() . ' (' . get_class($entity) .') provided');
        }
    }

    /**
     * @param int $id
     * @return EntityManager_EntityInterface|bool
     */
    private function _getFromIdentityMap($id)
    {
        $entityClassName = $this->_getEntityClassName();
        return $this->_identityMap->exists($entityClassName, $id);
    }

    /**
     * @param EntityManager_EntityInterface $entity
     * @return EntityManager_Mapper_AbstractMapper
     */
    private function _addToIdentityMap(EntityManager_EntityInterface $entity)
    {
        $this->_identityMap->add($entity);
    }

}
