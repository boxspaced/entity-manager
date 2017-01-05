<?php
namespace Boxspaced\EntityManager\Test\Double;

use Boxspaced\EntityManager\Mapper\MapperFactory;

class MapperFactoryDouble extends MapperFactory
{

    public $mapper;

    public function __construct()
    {

    }

    public function createForType($type)
    {
        if (null === $this->mapper) {
            $this->mapper = new MapperDouble();
        }

        return $this->mapper;
    }

}
