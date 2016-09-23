<?php
namespace EntityManager;

use EntityManager\Entity\EntityInterface;

class IdentityMap
{

    /**
     * @var array
     */
    protected $map = [];

    /**
     * @param EntityInterface $entity
     * @return IdentityMap
     */
    public function add(EntityInterface $entity)
    {
        $this->map[$this->globalKey($entity)] = $entity;
        return $this;
    }

    /**
     * @param string $type
     * @param int $id
     * @return EntityInterface
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
     * @param EntityInterface $entity
     * @return string
     */
    protected function globalKey(EntityInterface $entity)
    {
        $key = get_class($entity) . '.' . $entity->getId();
        return $key;
    }

}
