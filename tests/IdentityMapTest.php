<?php

require_once '_doubles/EntityStub.php';

class IdentityMapTest extends PHPUnit_Framework_TestCase
{

    protected $_identityMap;

    public function setUp()
    {
        $this->_identityMap = new EntityManager_IdentityMap();
    }

    public function testEntityNotExistsWhenMapEmpty()
    {
        $entity = $this->_createEntityStub();

        $result = $this->_identityMap->exists(get_class($entity), $entity->getId());

        $this->assertFalse($result);
    }

    public function testEntityNotExistsWhenMapNotEmpty()
    {
        $entity1 = $this->_createEntityStub();
        $entity2 = $this->_createEntityStub();
        $entity3 = $this->_createEntityStub();
        $entity = $this->_createEntityStub();

        $this->_identityMap->add($entity1);
        $this->_identityMap->add($entity2);
        $this->_identityMap->add($entity3);
        $result = $this->_identityMap->exists(get_class($entity), $entity->getId());

        $this->assertFalse($result);
    }

    public function testEntityExistsWhenHasBeenAddedAndMapNotEmpty()
    {
        $entity1 = $this->_createEntityStub();
        $entity2 = $this->_createEntityStub();
        $entity3 = $this->_createEntityStub();
        $entity = $this->_createEntityStub();

        $this->_identityMap->add($entity1);
        $this->_identityMap->add($entity2);
        $this->_identityMap->add($entity3);
        $this->_identityMap->add($entity);
        $result = $this->_identityMap->exists(get_class($entity), $entity->getId());

        $this->assertEquals($entity, $result);
    }

    protected function _createEntityStub()
    {
        $stub = new EntityStub();
        return $stub;
    }

}
