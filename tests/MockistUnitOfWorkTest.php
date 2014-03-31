<?php

class MockistUnitOfWorkTest extends PHPUnit_Framework_TestCase
{

    public function testEntitiesStoredInternally()
    {
        $stubEntityNew = $this->_createEntityStub(1);
        $stubEntityDirty = $this->_createEntityStub(2);
        $stubEntityDelete = $this->_createEntityStub(3);

        $mockProcessor = $this->getMock(
                'EntityManager_UnitOfWorkProcessorInterface',
                array('process')
        );
        $mockProcessor->expects($this->once())
                ->method('process')
                ->with(
                    $this->contains($stubEntityNew),
                    $this->contains($stubEntityDirty),
                    $this->contains($stubEntityDelete)
                );

        $unitOfWork = new EntityManager_UnitOfWork($mockProcessor);
        $unitOfWork->persist($stubEntityNew);
        $unitOfWork->dirty($stubEntityDirty);
        $unitOfWork->delete($stubEntityDelete);
        $unitOfWork->flush();
    }

    public function testAddingNewEntityMoreThanOnceDoesntDuplicate()
    {
        $stubEntity = $this->_createEntityStub(1);

        $mockProcessor = $this->getMock(
                'EntityManager_UnitOfWorkProcessorInterface',
                array('process')
        );
        $mockProcessor->expects($this->once())
                ->method('process')
                ->with(
                    $this->equalTo(array($stubEntity)),
                    $this->anything(),
                    $this->anything()
                );

        $unitOfWork = new EntityManager_UnitOfWork($mockProcessor);
        $unitOfWork->persist($stubEntity);
        $unitOfWork->persist($stubEntity);
        $unitOfWork->flush();
    }

    public function testNewEntityCannotBeMadeDirty()
    {
        $stubEntity = $this->_createEntityStub(1);

        $mockProcessor = $this->getMock(
                'EntityManager_UnitOfWorkProcessorInterface',
                array('process')
        );
        $mockProcessor->expects($this->once())
                ->method('process')
                ->with(
                    $this->anything(),
                    $this->equalTo(array()),
                    $this->anything()
                );

        $unitOfWork = new EntityManager_UnitOfWork($mockProcessor);
        $unitOfWork->persist($stubEntity);
        $unitOfWork->dirty($stubEntity);
        $unitOfWork->flush();
    }

    public function testCleanRemovesAllFromInternalStorage()
    {
        $stubEntityNew = $this->_createEntityStub(1);
        $stubEntityDirty = $this->_createEntityStub(2);
        $stubEntityDelete = $this->_createEntityStub(3);

        $mockProcessor = $this->getMock(
                'EntityManager_UnitOfWorkProcessorInterface',
                array('process')
        );
        $mockProcessor->expects($this->once())
                ->method('process')
                ->with(
                    $this->equalTo(array()),
                    $this->equalTo(array()),
                    $this->equalTo(array())
                );

        $unitOfWork = new EntityManager_UnitOfWork($mockProcessor);
        $unitOfWork->persist($stubEntityNew);
        $unitOfWork->dirty($stubEntityDirty);
        $unitOfWork->delete($stubEntityDelete);
        $unitOfWork->clean($stubEntityNew);
        $unitOfWork->clean($stubEntityDirty);
        $unitOfWork->clean($stubEntityDelete);
        $unitOfWork->flush();
    }

    public function testFlushClearsInternalStorageAfterProcessing()
    {
        $stubEntityNew = $this->_createEntityStub(1);
        $stubEntityDirty = $this->_createEntityStub(2);
        $stubEntityDelete = $this->_createEntityStub(3);

        $mockProcessor = $this->getMock(
                'EntityManager_UnitOfWorkProcessorInterface',
                array('process')
        );
        $mockProcessor->expects($this->at(1)) // Second time it is called
                ->method('process')
                ->with(
                    $this->equalTo(array()),
                    $this->equalTo(array()),
                    $this->equalTo(array())
                );

        $unitOfWork = new EntityManager_UnitOfWork($mockProcessor);
        $unitOfWork->persist($stubEntityNew);
        $unitOfWork->dirty($stubEntityDirty);
        $unitOfWork->delete($stubEntityDelete);
        $unitOfWork->flush(); // Should clear internal storage
        $unitOfWork->flush();
    }

    protected function _createEntityStub($id)
    {
        $stub = $this->getMock('EntityManager_EntityInterface');
        $stub->expects($this->any())
                ->method('getId')
                ->will($this->returnValue($id));
        return $stub;
    }

}
