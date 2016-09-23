<?php
namespace EntityManager\Test;

use EntityManager\IdentityMap;
use EntityManager\Test\Double\Entity;

class IdentityMapTest extends \PHPUnit_Framework_TestCase
{

    protected $identityMap;

    public function setUp()
    {
        $this->identityMap = new IdentityMap();
    }

    public function testEntityNotExistsWhenMapEmpty()
    {
        $entity = new Entity();

        $result = $this->identityMap->exists(get_class($entity), $entity->getId());

        $this->assertFalse($result);
    }

    public function testEntityNotExistsWhenMapNotEmpty()
    {
        $entity1 = new Entity();
        $entity2 = new Entity();
        $entity3 = new Entity();
        $entity = new Entity();

        $this->identityMap->add($entity1);
        $this->identityMap->add($entity2);
        $this->identityMap->add($entity3);

        $result = $this->identityMap->exists(get_class($entity), $entity->getId());

        $this->assertFalse($result);
    }

    public function testEntityExistsWhenHasBeenAddedAndMapNotEmpty()
    {
        $entity1 = new Entity();
        $entity2 = new Entity();
        $entity3 = new Entity();
        $entity = new Entity();

        $this->identityMap->add($entity1);
        $this->identityMap->add($entity2);
        $this->identityMap->add($entity3);
        $this->identityMap->add($entity);

        $result = $this->identityMap->exists(get_class($entity), $entity->getId());

        $this->assertEquals($entity, $result);
    }

}
