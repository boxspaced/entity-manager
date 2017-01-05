<?php
namespace Boxspaced\EntityManager\Test;

use Boxspaced\EntityManager\UnitOfWork;
use Boxspaced\EntityManager\Test\Double\MapperFactoryDouble;
use Boxspaced\EntityManager\Test\Double\EntityDouble;

class UnitOfWorkTest extends \PHPUnit_Framework_TestCase
{

    protected $mapperFactory;

    protected $unitOfWork;

    public function setUp()
    {
        $this->mapperFactory = new MapperFactoryDouble();
        $this->mapperFactory->createForType(null); // Force creation of mapper
        $this->unitOfWork = new UnitOfWork($this->mapperFactory);
    }

    public function testPersistedEntityWillBeInserted()
    {
        $entity = new EntityDouble();

        $this->unitOfWork->persist($entity);
        $this->unitOfWork->flush();

        $this->assertContains($entity, $this->mapperFactory->mapper->inserted);
        $this->assertEquals(1, count($this->mapperFactory->mapper->inserted));
    }

    public function testDirtyEntityWillBeUpdated()
    {
        $entity = new EntityDouble();

        $this->unitOfWork->dirty($entity);
        $this->unitOfWork->flush();

        $this->assertContains($entity, $this->mapperFactory->mapper->updated);
        $this->assertEquals(1, count($this->mapperFactory->mapper->updated));
    }

    public function testDeletedEntityWillBeDeleted()
    {
        $entity = new EntityDouble();

        $this->unitOfWork->delete($entity);
        $this->unitOfWork->flush();

        $this->assertContains($entity, $this->mapperFactory->mapper->deleted);
        $this->assertEquals(1, count($this->mapperFactory->mapper->deleted));
    }

    public function testPersistingNewEntityMoreThanOnceDoesntDuplicate()
    {
        $entity = new EntityDouble();

        $this->unitOfWork->persist($entity);
        $this->unitOfWork->persist($entity);
        $this->unitOfWork->flush();

        $this->assertEquals([$entity], $this->mapperFactory->mapper->inserted);
    }

    public function testNewEntityCannotBeMadeDirty()
    {
        $entity = new EntityDouble();

        $this->unitOfWork->persist($entity);
        $this->unitOfWork->dirty($entity);
        $this->unitOfWork->flush();

        $this->assertEquals([], $this->mapperFactory->mapper->updated);
    }

    public function testCleanClearsAllEntities()
    {
        $new = new EntityDouble();
        $dirty = new EntityDouble();
        $delete = new EntityDouble();

        $this->unitOfWork->persist($new);
        $this->unitOfWork->dirty($dirty);
        $this->unitOfWork->delete($delete);
        $this->unitOfWork->clean($new);
        $this->unitOfWork->clean($dirty);
        $this->unitOfWork->clean($delete);
        $this->unitOfWork->flush();

        $this->assertEquals([], $this->mapperFactory->mapper->inserted);
        $this->assertEquals([], $this->mapperFactory->mapper->updated);
        $this->assertEquals([], $this->mapperFactory->mapper->deleted);
    }

    public function testFlushClearsAllEntities()
    {
        $new = new EntityDouble();
        $dirty = new EntityDouble();
        $delete = new EntityDouble();

        $this->unitOfWork->persist($new);
        $this->unitOfWork->dirty($dirty);
        $this->unitOfWork->delete($delete);

        $this->unitOfWork->flush(); // Should clear internal storage

        $this->mapperFactory->mapper->inserted = [];
        $this->mapperFactory->mapper->updated = [];
        $this->mapperFactory->mapper->deleted = [];

        $this->unitOfWork->flush();

        $this->assertEquals([], $this->mapperFactory->mapper->inserted);
        $this->assertEquals([], $this->mapperFactory->mapper->updated);
        $this->assertEquals([], $this->mapperFactory->mapper->deleted);
    }

}
