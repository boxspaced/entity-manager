<?php

require_once '_doubles/UnitOfWorkProcessorSpy.php';
require_once '_doubles/EntityStub.php';

class UnitOfWorkTest extends PHPUnit_Framework_TestCase
{

    protected $_processorSpy;
    protected $_unitOfWork;

    public function setUp()
    {
        $this->_processorSpy = new UnitOfWorkProcessorSpy();
        $this->_unitOfWork = new EntityManager_UnitOfWork($this->_processorSpy);
    }

    public function testEntitiesStoredInternally()
    {
        $entityNew = $this->_createEntityStub();
        $entityDirty = $this->_createEntityStub();
        $entityDelete = $this->_createEntityStub();

        $this->_unitOfWork->persist($entityNew);
        $this->_unitOfWork->dirty($entityDirty);
        $this->_unitOfWork->delete($entityDelete);
        $this->_unitOfWork->flush();

        $this->assertContains($entityNew, $this->_processorSpy->new);
        $this->assertContains($entityDirty, $this->_processorSpy->dirty);
        $this->assertContains($entityDelete, $this->_processorSpy->delete);
        $this->assertEquals(1, count($this->_processorSpy->new));
        $this->assertEquals(1, count($this->_processorSpy->dirty));
        $this->assertEquals(1, count($this->_processorSpy->delete));
    }

    public function testAddingNewEntityMoreThanOnceDoesntDuplicate()
    {
        $entity = $this->_createEntityStub();

        $this->_unitOfWork->persist($entity);
        $this->_unitOfWork->persist($entity);
        $this->_unitOfWork->flush();

        $this->assertEquals(array($entity), $this->_processorSpy->new);
    }

    public function testNewEntityCannotBeMadeDirty()
    {
        $entity = $this->_createEntityStub();

        $this->_unitOfWork->persist($entity);
        $this->_unitOfWork->dirty($entity);
        $this->_unitOfWork->flush();

        $this->assertEquals(array(), $this->_processorSpy->dirty);
    }

    public function testCleanRemovesAllFromInternalStorage()
    {
        $entityNew = $this->_createEntityStub();
        $entityDirty = $this->_createEntityStub();
        $entityDelete = $this->_createEntityStub();

        $this->_unitOfWork->persist($entityNew);
        $this->_unitOfWork->dirty($entityDirty);
        $this->_unitOfWork->delete($entityDelete);
        $this->_unitOfWork->clean($entityNew);
        $this->_unitOfWork->clean($entityDirty);
        $this->_unitOfWork->clean($entityDelete);
        $this->_unitOfWork->flush();

        $this->assertEquals(array(), $this->_processorSpy->new);
        $this->assertEquals(array(), $this->_processorSpy->dirty);
        $this->assertEquals(array(), $this->_processorSpy->delete);
    }

    public function testFlushClearsInternalStorageAfterProcessing()
    {
        $entityNew = $this->_createEntityStub();
        $entityDirty = $this->_createEntityStub();
        $entityDelete = $this->_createEntityStub();

        $this->_unitOfWork->persist($entityNew);
        $this->_unitOfWork->dirty($entityDirty);
        $this->_unitOfWork->delete($entityDelete);
        $this->_unitOfWork->flush(); // Should clear internal storage
        $this->_unitOfWork->flush();

        $this->assertEquals(array(), $this->_processorSpy->new);
        $this->assertEquals(array(), $this->_processorSpy->dirty);
        $this->assertEquals(array(), $this->_processorSpy->delete);
    }

    protected function _createEntityStub()
    {
        $stub = new EntityStub();
        return $stub;
    }

}
