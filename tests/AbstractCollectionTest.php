<?php

require_once '_doubles/DummyUnitOfWorkProcessor.php';
require_once '_concrete/Builder.php';
require_once '_concrete/Collection.php';

class AbstractCollectionTest extends PHPUnit_Framework_TestCase
{

    protected $unitOfWork;
    protected $collection;
    protected $rowset = array(
        array('id' => '1', 'title' => 'Mr', 'fname' => 'Tom', 'lname' => 'Jones'),
        array('id' => '2', 'title' => 'Mrs', 'fname' => 'Betty', 'lname' => 'Smith'),
        array('id' => '3', 'title' => 'Ms', 'fname' => 'Jenny', 'lname' => 'Gumpert'),
        array('id' => '4', 'title' => 'Miss', 'fname' => 'Liz', 'lname' => 'McGuire'),
        array('id' => '5', 'title' => 'Mr', 'fname' => 'Terry', 'lname' => 'Fawlty'),
    );

    public function setUp()
    {
        $processor = new DummyUnitOfWorkProcessor();
        $this->unitOfWork = new EntityManager_UnitOfWork($processor);
        $identityMap = new EntityManager_IdentityMap();
        $builder = new Builder($identityMap, $this->unitOfWork);
        $this->collection = new Collection($builder, function() {
            return $this->rowset;
        });
    }

    public function testEmptyCollectionIsNotIteratedOver()
    {
        $this->collection->clear();

        foreach ($this->collection as $element) {
            $this->fail();
        }
    }

    public function testEmptyCollectionCountIsZero()
    {
        $this->collection->clear();

        $this->assertInternalType('int', count($this->collection));
        $this->assertEquals(0, count($this->collection));
    }

    public function testCollectionIterationProducesCorrectKeys()
    {
        $i = 0;
        foreach ($this->collection as $key => $element) {
            $this->assertEquals($i, $key);
            $i++;
        }
    }

    public function testNextElementNotValidWhenAtEndOfCollection()
    {
        $this->collection->last();
        $this->collection->next();

        $this->assertFalse($this->collection->valid());
    }

    public function testPrevElementNotValidWhenAtBeginningOfCollection()
    {
        $this->collection->first();
        $this->collection->prev();

        $this->assertFalse($this->collection->valid());
    }

    public function testRewindCollectionMovesToFirstElement()
    {
        $this->collection->last();
        $this->collection->rewind();

        $this->assertEquals($this->collection->current()->getId(), $this->rowset[0]['id']);
    }

    public function testFirstingCollectionMovesToFirstElement()
    {
        $this->collection->last();
        $this->collection->first();

        $this->assertEquals($this->collection->current()->getId(), $this->rowset[0]['id']);
    }

    public function testLastingCollectionMovesToLastElement()
    {
        $this->collection->first();
        $this->collection->last();

        $this->assertEquals(
            $this->collection->current()->getId(),
            $this->rowset[count($this->rowset)-1]['id']
        );
    }

    public function testGetKeysReturnsCorrectKeys()
    {
        $keys = $this->collection->getKeys();

        $this->assertEquals(array(0,1,2,3,4), $keys);
    }

    public function testCollectionReturnsOnlyEntityInstancesOnIteration()
    {
        $this->assertContainsOnly('Entity', $this->collection);
    }

    public function testAddWillNotAcceptWrongEntityType()
    {
        $this->setExpectedException('EntityManager_Collection_Exception');
        $badEntity = $this->getMock('EntityManager_EntityInterface', array(), array(), '');

        $this->collection->add($badEntity);
    }

    public function testAddEntityToCollectionIncrementsCountByOne()
    {
        $entity = new Entity($this->unitOfWork);
        $this->collection->add($entity);

        $this->assertEquals(count($this->rowset)+1, count($this->collection));
    }

    public function testRemoveEntityFromCollectionDecrementsCountByOne()
    {
        $this->collection->remove(1);

        $this->assertEquals(count($this->rowset)-1, count($this->collection));
    }

    public function testCanIterateProperlyOverCollectionWhenEntityRemovedFromBeginning()
    {
        $this->collection->remove(0);

        $actual = array();
        foreach ($this->collection as $entity) {
            $actual[] = $entity->getId();
        }

        $this->assertEquals(array(2,3,4,5), $actual);
    }

    public function testCanIterateProperlyOverCollectionWhenEntityRemovedFromMiddle()
    {
        $this->collection->remove(1);

        $actual = array();
        foreach ($this->collection as $entity) {
            $actual[] = $entity->getId();
        }

        $this->assertEquals(array(1,3,4,5), $actual);
    }

    public function testCanIterateProperlyOverCollectionWhenMultipleEntitiesRemovedFromMiddle()
    {
        $this->collection->remove(1);
        $this->collection->remove(2);
        $this->collection->remove(3);

        $actual = array();
        foreach ($this->collection as $entity) {
            $actual[] = $entity->getId();
        }

        $this->assertEquals(array(1,5), $actual);
    }

    public function testCanIterateProperlyOverCollectionWhenEntityRemovedFromEnd()
    {
        $this->collection->remove(4);

        $actual = array();
        foreach ($this->collection as $entity) {
            $actual[] = $entity->getId();
        }

        $this->assertEquals(array(1,2,3,4), $actual);
    }

    public function testCanIterateAndRemoveEntityFromBeginningSimultaneously()
    {
        $actual = array();
        foreach ($this->collection as $key => $entity) {
            if ($entity->getId() == 1) {
                $this->collection->remove($key);
            } else {
                $actual[] = $entity->getId();
            }
        }

        $this->assertEquals(array(2,3,4,5), $actual);
    }

    public function testCanIterateAndRemoveEntityFromMiddleSimultaneously()
    {
        $actual = array();
        foreach ($this->collection as $key => $entity) {
            if ($entity->getId() == 2) {
                $this->collection->remove($key);
            } else {
                $actual[] = $entity->getId();
            }
        }

        $this->assertEquals(array(1,3,4,5), $actual);
    }

    public function testCanIterateAndRemoveMultipleEntitiesFromMiddleSimultaneously()
    {
        $actual = array();
        foreach ($this->collection as $key => $entity) {
            if (in_array($entity->getId(), array(2,3,4))) {
                $this->collection->remove($key);
            } else {
                $actual[] = $entity->getId();
            }
        }

        $this->assertEquals(array(1,5), $actual);
    }

    public function testCanIterateAndRemoveEntityFromEndSimultaneously()
    {
        $actual = array();
        foreach ($this->collection as $key => $entity) {
            if ($entity->getId() == 5) {
                $this->collection->remove($key);
            } else {
                $actual[] = $entity->getId();
            }
        }

        $this->assertEquals(array(1,2,3,4), $actual);
    }

    public function testFilteringReturnsCorrectEntities()
    {
        $filtered = $this->collection->filter(function($entity) {
            return $entity->getTitle() == 'Mr';
        });

        $actual = array();
        foreach ($filtered as $entity) {
            $actual[] = $entity->getId();
        }

        $this->assertInstanceOf('Collection', $filtered);
        $this->assertEquals(array(1,5), $actual);
    }

}
