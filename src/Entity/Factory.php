<?php
namespace EntityManager\Entity;

use Pimple\Container;
use InvalidArgumentException;
use UnexpectedValueException;

class Factory
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
     * @throws InvalidArgumentException
     * @throws UnexpectedValueException
     */
    public function create($type)
    {
        if (!class_exists($type)) {
            throw new InvalidArgumentException("Entity class is not defined for type: {$type}");
        }

        $entity = new $type(
            $this->container['unitOfWork'],
            $this->container['config']
        );

        if (!($entity instanceof \EntityManager\Entity\AbstractEntity)) {

            throw new UnexpectedValueException(
                sprintf('Object is not an entity: %s', get_class($entity))
            );
        }

        return $entity;
    }

}
