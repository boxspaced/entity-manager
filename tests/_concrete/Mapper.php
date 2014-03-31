<?php

class Mapper extends EntityManager_Mapper_AbstractMapper
{

    protected $adapter;

    public function __construct(
        EntityManager_IdentityMap $identityMap,
        Builder $builder,
        Zend_Db_Adapter_Abstract $adapter
    )
    {
        parent::__construct($identityMap, $builder);
        $this->adapter = $adapter;
    }

    protected function getEntityClassName()
    {
        return 'Entity';
    }

    protected function doFind($id)
    {
        $select = '';
        $row = $this->adapter->fetchRow($select);
        return $row;
    }

    protected function doFindOne(EntityManager_Mapper_Conditions_Conditions $conditions = null)
    {
        $select = '';
        $row = $this->adapter->fetchRow($select);
        return $row;
    }

    protected function doFindAll(EntityManager_Mapper_Conditions_Conditions $conditions = null)
    {
        $select = '';
        $rows = $this->adapter->fetchAll($select);
        return $rows;
    }

    protected function doInsert(EntityManager_EntityInterface $entity)
    {
        $id = $this->adapter->lastInsertId();
        $entity->setId($id);
        return $this;
    }

    protected function doUpdate(EntityManager_EntityInterface $entity)
    {
        return $this;
    }

    protected function doDelete(EntityManager_EntityInterface $entity)
    {
        return $this;
    }

}
