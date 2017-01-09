<?php
namespace Boxspaced\EntityManager\Entity;

use Pimple\Container;
use Boxspaced\EntityManager\Exception;

class EntityFactory
{

    /**
     * @var Container
     */
    protected $container;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param string $type
     * @return AbstractEntity
     * @throws Exception\InvalidArgumentException
     * @throws Exception\UnexpectedValueException
     */
    public function create($type)
    {
        if (!class_exists($type)) {
            throw new Exception\InvalidArgumentException("Entity class is not defined for type: {$type}");
        }

        $entity = new $type(
            $this->container['unitOfWork'],
            $this->container['collectionFactory'],
            $this->container['config']
        );

        if (!($entity instanceof AbstractEntity)) {

            throw new Exception\UnexpectedValueException(
                sprintf('Object is not an entity: %s', get_class($entity))
            );
        }

        return $entity;
    }

}
