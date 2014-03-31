<?php

class Mapper extends EntityManager_Mapper_AbstractMapper
{

    protected $_adapter;

    public function __construct(
        EntityManager_IdentityMap $identityMap,
        Builder $builder,
        Zend_Db_Adapter_Abstract $adapter
    )
    {
        parent::__construct($identityMap, $builder);
        $this->_adapter = $adapter;
    }

    protected function _getEntityClassName()
    {
        return 'Entity';
    }

    protected function _find($id)
    {
        $select = '';
        $row = $this->_adapter->fetchRow($select);
        return $row;
    }

    protected function _findOne(EntityManager_Mapper_Conditions_Conditions $conditions = null)
    {
        $select = '';
        $row = $this->_adapter->fetchRow($select);
        return $row;
    }

    protected function _findAll(EntityManager_Mapper_Conditions_Conditions $conditions = null)
    {
        $select = '';
        $rows = $this->_adapter->fetchAll($select);
        return $rows;
    }

    protected function _insert(EntityManager_EntityInterface $entity)
    {
        $id = $this->_adapter->lastInsertId();
        $entity->setId($id);
        return $this;
    }

    protected function _update(EntityManager_EntityInterface $entity)
    {
        return $this;
    }

    protected function _delete(EntityManager_EntityInterface $entity)
    {
        return $this;
    }

}
