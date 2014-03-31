<?php

class EntityManager_IdentityMap
{

    /**
     * @var array
     */
    private $_map = array();

    /**
     * @param EntityManager_EntityInterface $entity
     * @return EntityManager_IdentityMap
     */
    public function add(EntityManager_EntityInterface $entity)
    {
        $this->_map[$this->_globalKey($entity)] = $entity;
        return $this;
    }

    /**
     * @param string $classname
     * @param int $id
     * @return EntityManager_EntityInterface|bool
     */
    public function exists($classname, $id)
    {
        $key = $classname . '.' . $id;
        if (isset($this->_map[$key])) {
            return $this->_map[$key];
        }
        return false;
    }

    /**
     * @param EntityManager_EntityInterface $entity
     * @return string
     */
    protected function _globalKey(EntityManager_EntityInterface $entity)
    {
        $key = get_class($entity) . '.' . $entity->getId();
        return $key;
    }

}
