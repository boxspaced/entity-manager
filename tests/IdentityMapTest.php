<?php
namespace Boxspaced\EntityManager\Test;

use Boxspaced\EntityManager\IdentityMap;

class IdentityMapTest extends \PHPUnit_Framework_TestCase
{

    protected $identityMap;

    public function setUp()
    {
        $this->identityMap = new IdentityMap();
    }

    public function testEntityNotExistsWhenMapEmpty()
    {
        $entity = new EntityDouble();

        $result = $this->identityMap->exists(get_class($entity), $entity->getId());

        $this->assertFalse($result);
    }

    public function testEntityNotExistsWhenMapNotEmpty()
    {
        $entity1 = new EntityDouble();
        $entity2 = new EntityDouble();
        $entity3 = new EntityDouble();
        $entity = new EntityDouble();

        $this->identityMap->add($entity1);
        $this->identityMap->add($entity2);
        $this->identityMap->add($entity3);

        $result = $this->identityMap->exists(get_class($entity), $entity->getId());

        $this->assertFalse($result);
    }

    public function testEntityExistsWhenHasBeenAddedAndMapNotEmpty()
    {
        $entity1 = new EntityDouble();
        $entity2 = new EntityDouble();
        $entity3 = new EntityDouble();
        $entity = new EntityDouble();

        $this->identityMap->add($entity1);
        $this->identityMap->add($entity2);
        $this->identityMap->add($entity3);
        $this->identityMap->add($entity);

        $result = $this->identityMap->exists(get_class($entity), $entity->getId());

        $this->assertEquals($entity, $result);
    }

}
