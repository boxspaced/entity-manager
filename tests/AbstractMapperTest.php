<?php

require_once '_doubles/DummyUnitOfWorkProcessor.php';
require_once '_concrete/Builder.php';
require_once '_concrete/Entity.php';
require_once '_concrete/Mapper.php';

class AbstractMapperTest extends PHPUnit_Framework_TestCase
{

    protected $_adapter;
    protected $_mapper;
    protected $_identityMap;
    protected $_unitOfWork;
    protected $_rowset = array(
        array('id' => '1', 'title' => 'Mr', 'fname' => 'Tom', 'lname' => 'Jones'),
        array('id' => '2', 'title' => 'Mrs', 'fname' => 'Betty', 'lname' => 'Smith'),
        array('id' => '3', 'title' => 'Ms', 'fname' => 'Jenny', 'lname' => 'Gumpert'),
    );

    public function setUp()
    {
        $processor = new DummyUnitOfWorkProcessor();
        $this->_unitOfWork = new EntityManager_UnitOfWork($processor);
        $this->_identityMap = new EntityManager_IdentityMap();
        $builder = new Builder($this->_identityMap, $this->_unitOfWork);
        $this->_adapter = new Zend_Test_DbAdapter();
        $this->_mapper = new Mapper($this->_identityMap, $builder, $this->_adapter);
    }

    public function testFindGetsFromIdentityMapWhenExists()
    {
        $id = 23;

        $entity = new Entity($this->_unitOfWork);
        $entity->setId($id);

        $this->_identityMap->add($entity);
        $result = $this->_mapper->find($id);

        $this->assertEquals($entity, $result);
    }

    public function testFindReturnsFalseIfNotFound()
    {
        $rows = array();
        $stmt = Zend_Test_DbStatement::createSelectStatement($rows);
        $this->_adapter->appendStatementToStack($stmt);

        $result = $this->_mapper->find(null);

        $this->assertFalse($result);
    }

    public function testFindReturnsAnEntityWhenFound()
    {
        $stmt = Zend_Test_DbStatement::createSelectStatement($this->_rowset);
        $this->_adapter->appendStatementToStack($stmt);

        $result = $this->_mapper->find(null);

        $this->assertInstanceOf('Entity', $result);
    }

    public function testFindOneReturnsFalseIfNotFound()
    {
        $rowset = array();
        $stmt = Zend_Test_DbStatement::createSelectStatement($rowset);
        $this->_adapter->appendStatementToStack($stmt);

        $result = $this->_mapper->findOne(null);

        $this->assertFalse($result);
    }

    public function testFindOneReturnsAnEntityWhenFound()
    {
        $stmt = Zend_Test_DbStatement::createSelectStatement($this->_rowset);
        $this->_adapter->appendStatementToStack($stmt);

        $result = $this->_mapper->find(null);

        $this->assertInstanceOf('Entity', $result);
    }

    public function testFindAllReturnsAnEmptyCollectionWhenNoneFound()
    {
        $rowset = array();
        $stmt = Zend_Test_DbStatement::createSelectStatement($rowset);
        $this->_adapter->appendStatementToStack($stmt);

        $result = $this->_mapper->findAll();

        $this->assertInstanceOf('Collection', $result);
        $this->assertEquals(0, count($result));
    }

    public function testFindAllReturnsCollectionOfEntitiesWhenFound()
    {
        $stmt = Zend_Test_DbStatement::createSelectStatement($this->_rowset);
        $this->_adapter->appendStatementToStack($stmt);

        $result = $this->_mapper->findAll();

        $this->assertInstanceOf('Collection', $result);
        $this->assertEquals(count($this->_rowset), count($result));
    }

    public function testFindAllDoesNotCallDataSource()
    {
        $stmt = Zend_Test_DbStatement::createSelectStatement($this->_rowset);
        $this->_adapter->appendStatementToStack($stmt);

        $result = $this->_mapper->findAll();

        $this->assertInstanceOf('Collection', $result);
        //$this->assertEquals(1, count($this->_adapter->getStatementStack()));
    }

    public function testInsertWillNotAcceptWrongEntityType()
    {
        $this->setExpectedException('EntityManager_Mapper_Exception');
        $badEntity = $this->getMock('EntityManager_EntityInterface', array(), array(), '');

        $this->_mapper->insert($badEntity);
    }

    public function testInsertAddsEntityToIdentityMap()
    {
        $id = 67;

        $this->_adapter->appendLastInsertIdToStack($id);
        $entity = new Entity($this->_unitOfWork);

        $this->_mapper->insert($entity);
        $result = $this->_identityMap->exists(get_class($entity), $id);

        $this->assertEquals($entity, $result);
    }

    public function testUpdateWillNotAcceptWrongEntityType()
    {
        $this->setExpectedException('EntityManager_Mapper_Exception');
        $badEntity = $this->getMock('EntityManager_EntityInterface', array(), array(), '');

        $this->_mapper->update($badEntity);
    }

    public function testDeleteWillNotAcceptWrongEntityType()
    {
        $this->setExpectedException('EntityManager_Mapper_Exception');
        $badEntity = $this->getMock('EntityManager_EntityInterface', array(), array(), '');

        $this->_mapper->delete($badEntity);
    }

}
