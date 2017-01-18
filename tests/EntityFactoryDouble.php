<?php
namespace Boxspaced\EntityManager\Test;

use Boxspaced\EntityManager\Entity\EntityFactory;

class EntityFactoryDouble extends EntityFactory
{

    public $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function create($type)
    {
        return new $type($this->config['types'][$type]['entity']);
    }

}
