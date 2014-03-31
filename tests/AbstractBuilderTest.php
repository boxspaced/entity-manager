<?php

require_once '_doubles/UnitOfWorkProcessorSpy.php';
require_once '_concrete/Builder.php';
require_once '_concrete/Entity.php';

class AbstractBuilderTest extends PHPUnit_Framework_TestCase
{

    protected $_processorSpy;
    protected $_unitOfWork;
    protected $_builder;
    protected $_identityMap;

    public function setUp()
    {
        $this->_processorSpy = new UnitOfWorkProcessorSpy();
        $this->_unitOfWork = new EntityManager_UnitOfWork($this->_processorSpy);
        $this->_identityMap = new EntityManager_IdentityMap();
        $this->_builder = new Builder($this->_identityMap, $this->_unitOfWork);
    }

    public function testBuildGetsFromIdentityMapWhenExists()
    {
        $id = 3;

        $entity = new Entity($this->_unitOfWork);
        $entity->setId($id);
        $this->_identityMap->add($entity);

        $row = array('id' => $id, 'title' => 'Ms', 'fname' => 'Jenny', 'lname' => 'Gumpert');
        $result = $this->_builder->build($row);

        $this->assertEquals($entity, $result);
    }

    public function testBuildReturnsNewEntityWhenNotInIdentityMap()
    {
        $row = array('id' => 49, 'title' => 'Ms', 'fname' => 'Jenny', 'lname' => 'Gumpert');
        $result = $this->_builder->build($row);

        $this->assertInstanceOf('Entity', $result);
    }

    public function testBuildAddsToIdentityMapWhenRowFromPersistantStorage()
    {
        $row = array('id' => 33, 'title' => 'Ms', 'fname' => 'Jenny', 'lname' => 'Gumpert');
        $entity = $this->_builder->build($row);

        $result = $this->_identityMap->exists(get_class($entity), $entity->getId());

        $this->assertEquals($entity, $result);
    }

    public function testBuildWillNotAcceptEmptyRow()
    {
        $this->setExpectedException('EntityManager_Builder_Exception');

        $row = array();
        $this->_builder->build($row);
    }

    public function testBuildWillNotAcceptRowWithoutId()
    {
        $this->setExpectedException('EntityManager_Builder_Exception');

        $row = array('title' => 'Ms', 'fname' => 'Jenny', 'lname' => 'Gumpert');
        $this->_builder->build($row);
    }

}
