<?php

require_once '_doubles/DummyUnitOfWorkProcessor.php';
require_once '_concrete/Builder.php';
require_once '_concrete/Collection.php';

class AbstractCollectionTest extends PHPUnit_Framework_TestCase
{

    protected $_unitOfWork;
    protected $_collection;
    protected $_rowset = array(
        array('id' => '1', 'title' => 'Mr', 'fname' => 'Tom', 'lname' => 'Jones'),
        array('id' => '2', 'title' => 'Mrs', 'fname' => 'Betty', 'lname' => 'Smith'),
        array('id' => '3', 'title' => 'Ms', 'fname' => 'Jenny', 'lname' => 'Gumpert'),
        array('id' => '4', 'title' => 'Miss', 'fname' => 'Liz', 'lname' => 'McGuire'),
        array('id' => '5', 'title' => 'Mr', 'fname' => 'Terry', 'lname' => 'Fawlty'),
    );

    public function setUp()
    {
        $processor = new DummyUnitOfWorkProcessor();
        $this->_unitOfWork = new EntityManager_UnitOfWork($processor);
        $identityMap = new EntityManager_IdentityMap();
        $builder = new Builder($identityMap, $this->_unitOfWork);
        $this->_collection = new Collection($builder, function() {
            return $this->_rowset;
        });
    }

    public function testEmptyCollectionIsNotIteratedOver()
    {
        $this->_collection->clear();

        foreach ($this->_collection as $element) {
            $this->fail();
        }
    }

    public function testEmptyCollectionCountIsZero()
    {
        $this->_collection->clear();

        $this->assertInternalType('int', count($this->_collection));
        $this->assertEquals(0, count($this->_collection));
    }

    public function testCollectionIterationProducesCorrectKeys()
    {
        $i = 0;
        foreach ($this->_collection as $key => $element) {
            $this->assertEquals($i, $key);
            $i++;
        }
    }

    public function testNextElementNotValidWhenAtEndOfCollection()
    {
        $this->_collection->last();
        $this->_collection->next();

        $this->assertFalse($this->_collection->valid());
    }

    public function testPrevElementNotValidWhenAtBeginningOfCollection()
    {
        $this->_collection->first();
        $this->_collection->prev();

        $this->assertFalse($this->_collection->valid());
    }

    public function testRewindCollectionMovesToFirstElement()
    {
        $this->_collection->last();
        $this->_collection->rewind();

        $this->assertEquals($this->_collection->current()->getId(), $this->_rowset[0]['id']);
    }

    public function testFirstingCollectionMovesToFirstElement()
    {
        $this->_collection->last();
        $this->_collection->first();

        $this->assertEquals($this->_collection->current()->getId(), $this->_rowset[0]['id']);
    }

    public function testLastingCollectionMovesToLastElement()
    {
        $this->_collection->first();
        $this->_collection->last();

        $this->assertEquals(
            $this->_collection->current()->getId(),
            $this->_rowset[count($this->_rowset)-1]['id']
        );
    }

    public function testGetKeysReturnsCorrectKeys()
    {
        $keys = $this->_collection->getKeys();

        $this->assertEquals(array(0,1,2,3,4), $keys);
    }

    public function testCollectionReturnsOnlyEntityInstancesOnIteration()
    {
        $this->assertContainsOnly('Entity', $this->_collection);
    }

    public function testAddWillNotAcceptWrongEntityType()
    {
        $this->setExpectedException('EntityManager_Collection_Exception');
        $badEntity = $this->getMock('EntityManager_EntityInterface', array(), array(), '');

        $this->_collection->add($badEntity);
    }

    public function testAddEntityToCollectionIncrementsCountByOne()
    {
        $entity = new Entity($this->_unitOfWork);
        $this->_collection->add($entity);

        $this->assertEquals(count($this->_rowset)+1, count($this->_collection));
    }

    public function testRemoveEntityFromCollectionDecrementsCountByOne()
    {
        $this->_collection->remove(1);

        $this->assertEquals(count($this->_rowset)-1, count($this->_collection));
    }

    public function testCanIterateProperlyOverCollectionWhenEntityRemovedFromBeginning()
    {
        $this->_collection->remove(0);

        $actual = array();
        foreach ($this->_collection as $entity) {
            $actual[] = $entity->getId();
        }

        $this->assertEquals(array(2,3,4,5), $actual);
    }

    public function testCanIterateProperlyOverCollectionWhenEntityRemovedFromMiddle()
    {
        $this->_collection->remove(1);

        $actual = array();
        foreach ($this->_collection as $entity) {
            $actual[] = $entity->getId();
        }

        $this->assertEquals(array(1,3,4,5), $actual);
    }

    public function testCanIterateProperlyOverCollectionWhenMultipleEntitiesRemovedFromMiddle()
    {
        $this->_collection->remove(1);
        $this->_collection->remove(2);
        $this->_collection->remove(3);

        $actual = array();
        foreach ($this->_collection as $entity) {
            $actual[] = $entity->getId();
        }

        $this->assertEquals(array(1,5), $actual);
    }

    public function testCanIterateProperlyOverCollectionWhenEntityRemovedFromEnd()
    {
        $this->_collection->remove(4);

        $actual = array();
        foreach ($this->_collection as $entity) {
            $actual[] = $entity->getId();
        }

        $this->assertEquals(array(1,2,3,4), $actual);
    }

    public function testCanIterateAndRemoveEntityFromBeginningSimultaneously()
    {
        $actual = array();
        foreach ($this->_collection as $key => $entity) {
            if ($entity->getId() == 1) {
                $this->_collection->remove($key);
            } else {
                $actual[] = $entity->getId();
            }
        }

        $this->assertEquals(array(2,3,4,5), $actual);
    }

    public function testCanIterateAndRemoveEntityFromMiddleSimultaneously()
    {
        $actual = array();
        foreach ($this->_collection as $key => $entity) {
            if ($entity->getId() == 2) {
                $this->_collection->remove($key);
            } else {
                $actual[] = $entity->getId();
            }
        }

        $this->assertEquals(array(1,3,4,5), $actual);
    }

    public function testCanIterateAndRemoveMultipleEntitiesFromMiddleSimultaneously()
    {
        $actual = array();
        foreach ($this->_collection as $key => $entity) {
            if (in_array($entity->getId(), array(2,3,4))) {
                $this->_collection->remove($key);
            } else {
                $actual[] = $entity->getId();
            }
        }

        $this->assertEquals(array(1,5), $actual);
    }

    public function testCanIterateAndRemoveEntityFromEndSimultaneously()
    {
        $actual = array();
        foreach ($this->_collection as $key => $entity) {
            if ($entity->getId() == 5) {
                $this->_collection->remove($key);
            } else {
                $actual[] = $entity->getId();
            }
        }

        $this->assertEquals(array(1,2,3,4), $actual);
    }

    public function testFilteringReturnsCorrectEntities()
    {
        $filtered = $this->_collection->filter(function($entity) {
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
