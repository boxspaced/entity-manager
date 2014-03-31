<?php

require_once '_doubles/EntityStub.php';

class IdentityMapTest extends PHPUnit_Framework_TestCase
{

    protected $identityMap;

    public function setUp()
    {
        $this->identityMap = new EntityManager_IdentityMap();
    }

    public function testEntityNotExistsWhenMapEmpty()
    {
        $entity = $this->createEntityStub();

        $result = $this->identityMap->exists(get_class($entity), $entity->getId());

        $this->assertFalse($result);
    }

    public function testEntityNotExistsWhenMapNotEmpty()
    {
        $entity1 = $this->createEntityStub();
        $entity2 = $this->createEntityStub();
        $entity3 = $this->createEntityStub();
        $entity = $this->createEntityStub();

        $this->identityMap->add($entity1);
        $this->identityMap->add($entity2);
        $this->identityMap->add($entity3);
        $result = $this->identityMap->exists(get_class($entity), $entity->getId());

        $this->assertFalse($result);
    }

    public function testEntityExistsWhenHasBeenAddedAndMapNotEmpty()
    {
        $entity1 = $this->createEntityStub();
        $entity2 = $this->createEntityStub();
        $entity3 = $this->createEntityStub();
        $entity = $this->createEntityStub();

        $this->identityMap->add($entity1);
        $this->identityMap->add($entity2);
        $this->identityMap->add($entity3);
        $this->identityMap->add($entity);
        $result = $this->identityMap->exists(get_class($entity), $entity->getId());

        $this->assertEquals($entity, $result);
    }

    protected function createEntityStub()
    {
        $stub = new EntityStub();
        return $stub;
    }

}
