<?php

require_once '_doubles/UnitOfWorkProcessorSpy.php';
require_once '_concrete/Builder.php';
require_once '_concrete/Entity.php';

class AbstractBuilderTest extends PHPUnit_Framework_TestCase
{

    protected $processorSpy;
    protected $unitOfWork;
    protected $builder;
    protected $identityMap;

    public function setUp()
    {
        $this->processorSpy = new UnitOfWorkProcessorSpy();
        $this->unitOfWork = new EntityManager_UnitOfWork($this->processorSpy);
        $this->identityMap = new EntityManager_IdentityMap();
        $this->builder = new Builder($this->identityMap, $this->unitOfWork);
    }

    public function testBuildGetsFromIdentityMapWhenExists()
    {
        $id = 3;

        $entity = new Entity($this->unitOfWork);
        $entity->setId($id);
        $this->identityMap->add($entity);

        $row = array('id' => $id, 'title' => 'Ms', 'fname' => 'Jenny', 'lname' => 'Gumpert');
        $result = $this->builder->build($row);

        $this->assertEquals($entity, $result);
    }

    public function testBuildReturnsNewEntityWhenNotInIdentityMap()
    {
        $row = array('id' => 49, 'title' => 'Ms', 'fname' => 'Jenny', 'lname' => 'Gumpert');
        $result = $this->builder->build($row);

        $this->assertInstanceOf('Entity', $result);
    }

    public function testBuildAddsToIdentityMapWhenRowFromPersistantStorage()
    {
        $row = array('id' => 33, 'title' => 'Ms', 'fname' => 'Jenny', 'lname' => 'Gumpert');
        $entity = $this->builder->build($row);

        $result = $this->identityMap->exists(get_class($entity), $entity->getId());

        $this->assertEquals($entity, $result);
    }

    public function testBuildWillNotAcceptEmptyRow()
    {
        $this->setExpectedException('EntityManager_Builder_Exception');

        $row = array();
        $this->builder->build($row);
    }

    public function testBuildWillNotAcceptRowWithoutId()
    {
        $this->setExpectedException('EntityManager_Builder_Exception');

        $row = array('title' => 'Ms', 'fname' => 'Jenny', 'lname' => 'Gumpert');
        $this->builder->build($row);
    }

}
