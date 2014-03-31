<?php

class EntityManager_IdentityMap
{

    /**
     * @var array
     */
    private $map = array();

    /**
     * @param EntityManager_EntityInterface $entity
     * @return EntityManager_IdentityMap
     */
    public function add(EntityManager_EntityInterface $entity)
    {
        $this->map[$this->globalKey($entity)] = $entity;
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
        if (isset($this->map[$key])) {
            return $this->map[$key];
        }
        return false;
    }

    /**
     * @param EntityManager_EntityInterface $entity
     * @return string
     */
    protected function globalKey(EntityManager_EntityInterface $entity)
    {
        $key = get_class($entity) . '.' . $entity->getId();
        return $key;
    }

}
