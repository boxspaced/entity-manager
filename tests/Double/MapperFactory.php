<?php
namespace Boxspaced\EntityManager\Test\Double;

class MapperFactory extends \Boxspaced\EntityManager\Mapper\Factory
{

    public $mapper;

    public function __construct()
    {

    }

    public function createForType($type)
    {
        if (null === $this->mapper) {
            $this->mapper = new Mapper();
        }

        return $this->mapper;
    }

}
