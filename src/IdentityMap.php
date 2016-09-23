<?php
namespace EntityManager;

use EntityManager\Entity\AbstractEntity;

class IdentityMap
{

    /**
     * @var array
     */
    protected $map = [];

    /**
     * @param AbstractEntity $entity
     * @return IdentityMap
     */
    public function add(AbstractEntity $entity)
    {
        $this->map[$this->globalKey($entity)] = $entity;
        return $this;
    }

    /**
     * @param string $type
     * @param int $id
     * @return AbstractEntity
     */
    public function exists($type, $id)
    {
        $key = $type . '.' . $id;
        if (isset($this->map[$key])) {
            return $this->map[$key];
        }
        return false;
    }

    /**
     * @param AbstractEntity $entity
     * @return string
     */
    protected function globalKey(AbstractEntity $entity)
    {
        $key = get_class($entity) . '.' . $entity->getId();
        return $key;
    }

}
