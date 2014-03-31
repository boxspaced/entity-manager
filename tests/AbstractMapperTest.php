<?php

require_once '_doubles/DummyUnitOfWorkProcessor.php';
require_once '_concrete/Builder.php';
require_once '_concrete/Entity.php';
require_once '_concrete/Mapper.php';

class AbstractMapperTest extends PHPUnit_Framework_TestCase
{

    protected $adapter;
    protected $mapper;
    protected $identityMap;
    protected $unitOfWork;
    protected $rowset = array(
        array('id' => '1', 'title' => 'Mr', 'fname' => 'Tom', 'lname' => 'Jones'),
        array('id' => '2', 'title' => 'Mrs', 'fname' => 'Betty', 'lname' => 'Smith'),
        array('id' => '3', 'title' => 'Ms', 'fname' => 'Jenny', 'lname' => 'Gumpert'),
    );

    public function setUp()
    {
        $processor = new DummyUnitOfWorkProcessor();
        $this->unitOfWork = new EntityManager_UnitOfWork($processor);
        $this->identityMap = new EntityManager_IdentityMap();
        $builder = new Builder($this->identityMap, $this->unitOfWork);
        $this->adapter = new Zend_Test_DbAdapter();
        $this->mapper = new Mapper($this->identityMap, $builder, $this->adapter);
    }

    public function testFindGetsFromIdentityMapWhenExists()
    {
        $id = 23;

        $entity = new Entity($this->unitOfWork);
        $entity->setId($id);

        $this->identityMap->add($entity);
        $result = $this->mapper->find($id);

        $this->assertEquals($entity, $result);
    }

    public function testFindReturnsFalseIfNotFound()
    {
        $rows = array();
        $stmt = Zend_Test_DbStatement::createSelectStatement($rows);
        $this->adapter->appendStatementToStack($stmt);

        $result = $this->mapper->find(null);

        $this->assertFalse($result);
    }

    public function testFindReturnsAnEntityWhenFound()
    {
        $stmt = Zend_Test_DbStatement::createSelectStatement($this->rowset);
        $this->adapter->appendStatementToStack($stmt);

        $result = $this->mapper->find(null);

        $this->assertInstanceOf('Entity', $result);
    }

    public function testFindOneReturnsFalseIfNotFound()
    {
        $rowset = array();
        $stmt = Zend_Test_DbStatement::createSelectStatement($rowset);
        $this->adapter->appendStatementToStack($stmt);

        $result = $this->mapper->findOne(null);

        $this->assertFalse($result);
    }

    public function testFindOneReturnsAnEntityWhenFound()
    {
        $stmt = Zend_Test_DbStatement::createSelectStatement($this->rowset);
        $this->adapter->appendStatementToStack($stmt);

        $result = $this->mapper->find(null);

        $this->assertInstanceOf('Entity', $result);
    }

    public function testFindAllReturnsAnEmptyCollectionWhenNoneFound()
    {
        $rowset = array();
        $stmt = Zend_Test_DbStatement::createSelectStatement($rowset);
        $this->adapter->appendStatementToStack($stmt);

        $result = $this->mapper->findAll();

        $this->assertInstanceOf('Collection', $result);
        $this->assertEquals(0, count($result));
    }

    public function testFindAllReturnsCollectionOfEntitiesWhenFound()
    {
        $stmt = Zend_Test_DbStatement::createSelectStatement($this->rowset);
        $this->adapter->appendStatementToStack($stmt);

        $result = $this->mapper->findAll();

        $this->assertInstanceOf('Collection', $result);
        $this->assertEquals(count($this->rowset), count($result));
    }

    public function testFindAllDoesNotCallDataSource()
    {
        $stmt = Zend_Test_DbStatement::createSelectStatement($this->rowset);
        $this->adapter->appendStatementToStack($stmt);

        $result = $this->mapper->findAll();

        $this->assertInstanceOf('Collection', $result);
        //$this->assertEquals(1, count($this->adapter->getStatementStack()));
    }

    public function testInsertWillNotAcceptWrongEntityType()
    {
        $this->setExpectedException('EntityManager_Mapper_Exception');
        $badEntity = $this->getMock('EntityManager_EntityInterface', array(), array(), '');

        $this->mapper->insert($badEntity);
    }

    public function testInsertAddsEntityToIdentityMap()
    {
        $id = 67;

        $this->adapter->appendLastInsertIdToStack($id);
        $entity = new Entity($this->unitOfWork);

        $this->mapper->insert($entity);
        $result = $this->identityMap->exists(get_class($entity), $id);

        $this->assertEquals($entity, $result);
    }

    public function testUpdateWillNotAcceptWrongEntityType()
    {
        $this->setExpectedException('EntityManager_Mapper_Exception');
        $badEntity = $this->getMock('EntityManager_EntityInterface', array(), array(), '');

        $this->mapper->update($badEntity);
    }

    public function testDeleteWillNotAcceptWrongEntityType()
    {
        $this->setExpectedException('EntityManager_Mapper_Exception');
        $badEntity = $this->getMock('EntityManager_EntityInterface', array(), array(), '');

        $this->mapper->delete($badEntity);
    }

}
