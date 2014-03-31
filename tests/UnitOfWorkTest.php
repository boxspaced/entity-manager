<?php

require_once '_doubles/UnitOfWorkProcessorSpy.php';
require_once '_doubles/EntityStub.php';

class UnitOfWorkTest extends PHPUnit_Framework_TestCase
{

    protected $processorSpy;
    protected $unitOfWork;

    public function setUp()
    {
        $this->processorSpy = new UnitOfWorkProcessorSpy();
        $this->unitOfWork = new EntityManager_UnitOfWork($this->processorSpy);
    }

    public function testEntitiesStoredInternally()
    {
        $entityNew = $this->_createEntityStub();
        $entityDirty = $this->_createEntityStub();
        $entityDelete = $this->_createEntityStub();

        $this->unitOfWork->persist($entityNew);
        $this->unitOfWork->dirty($entityDirty);
        $this->unitOfWork->delete($entityDelete);
        $this->unitOfWork->flush();

        $this->assertContains($entityNew, $this->processorSpy->new);
        $this->assertContains($entityDirty, $this->processorSpy->dirty);
        $this->assertContains($entityDelete, $this->processorSpy->delete);
        $this->assertEquals(1, count($this->processorSpy->new));
        $this->assertEquals(1, count($this->processorSpy->dirty));
        $this->assertEquals(1, count($this->processorSpy->delete));
    }

    public function testAddingNewEntityMoreThanOnceDoesntDuplicate()
    {
        $entity = $this->_createEntityStub();

        $this->unitOfWork->persist($entity);
        $this->unitOfWork->persist($entity);
        $this->unitOfWork->flush();

        $this->assertEquals(array($entity), $this->processorSpy->new);
    }

    public function testNewEntityCannotBeMadeDirty()
    {
        $entity = $this->_createEntityStub();

        $this->unitOfWork->persist($entity);
        $this->unitOfWork->dirty($entity);
        $this->unitOfWork->flush();

        $this->assertEquals(array(), $this->processorSpy->dirty);
    }

    public function testCleanRemovesAllFromInternalStorage()
    {
        $entityNew = $this->_createEntityStub();
        $entityDirty = $this->_createEntityStub();
        $entityDelete = $this->_createEntityStub();

        $this->unitOfWork->persist($entityNew);
        $this->unitOfWork->dirty($entityDirty);
        $this->unitOfWork->delete($entityDelete);
        $this->unitOfWork->clean($entityNew);
        $this->unitOfWork->clean($entityDirty);
        $this->unitOfWork->clean($entityDelete);
        $this->unitOfWork->flush();

        $this->assertEquals(array(), $this->processorSpy->new);
        $this->assertEquals(array(), $this->processorSpy->dirty);
        $this->assertEquals(array(), $this->processorSpy->delete);
    }

    public function testFlushClearsInternalStorageAfterProcessing()
    {
        $entityNew = $this->_createEntityStub();
        $entityDirty = $this->_createEntityStub();
        $entityDelete = $this->_createEntityStub();

        $this->unitOfWork->persist($entityNew);
        $this->unitOfWork->dirty($entityDirty);
        $this->unitOfWork->delete($entityDelete);
        $this->unitOfWork->flush(); // Should clear internal storage
        $this->unitOfWork->flush();

        $this->assertEquals(array(), $this->processorSpy->new);
        $this->assertEquals(array(), $this->processorSpy->dirty);
        $this->assertEquals(array(), $this->processorSpy->delete);
    }

    protected function _createEntityStub()
    {
        $stub = new EntityStub();
        return $stub;
    }

}
