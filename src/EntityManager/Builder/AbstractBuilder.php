<?php

abstract class EntityManager_Builder_AbstractBuilder
{

    /**
     * @var EntityManager_IdentityMap
     */
    protected $_identityMap;

    /**
     * @param array $row
     * @return EntityManager_EntityInterface
     */
    abstract protected function _buildEntity(array $row);

    /**
     * @param Callable|null $rowsetCallback
     * @return EntityManager_Collection_AbstractCollection
     */
    abstract public function createCollection(Callable $rowsetCallback = null);

    /**
     * @return string
     */
    abstract protected function _getEntityClassName();

    /**
     * @param EntityManager_IdentityMap $identityMap
     */
    public function __construct(
        EntityManager_IdentityMap $identityMap
    )
    {
        $this->_identityMap = $identityMap;
    }

    /**
     * @param array $row
     * @return EntityManager_EntityInterface
     */
    public function build(array $row)
    {
        if (!$row) {
            throw new EntityManager_Builder_Exception(
                'Row array empty, use factories to create new entities'
            );
        }
        if (!isset($row['id']) || !$row['id']) {
            throw new EntityManager_Builder_Exception(
                'No \'id\' index in row array, use factories to create new entities'
            );
        }

        $existing = $this->_getFromIdentityMap($row['id']);
        if ($existing) {
            return $existing;
        }

        $entity = $this->_buildEntity($row);
        $this->_addToIdentityMap($entity);

        return $entity;
    }

    /**
     * @param int $id
     * @return EntityManager_EntityInterface|bool
     */
    protected function _getFromIdentityMap($id)
    {
        $entityClassName = $this->_getEntityClassName();
        return $this->_identityMap->exists($entityClassName, $id);
    }

    /**
     * @param EntityManager_EntityInterface $entity
     * @return EntityManager_Builder_AbstractBuilder
     */
    protected function _addToIdentityMap(EntityManager_EntityInterface $entity)
    {
        $this->_identityMap->add($entity);
        return $this;
    }

}
