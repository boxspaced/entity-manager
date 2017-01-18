<?php
namespace Boxspaced\EntityManager\Test;

use Boxspaced\EntityManager\Collection\Collection;
use Boxspaced\EntityManager\Entity\AbstractEntity;
use Boxspaced\EntityManager\Exception;

class CollectionTest extends \PHPUnit_Framework_TestCase
{

    protected $collection;

    protected $rowset = [
        ['id' => 1, 'title' => 'Mr', 'fname' => 'Tom', 'lname' => 'Jones'],
        ['id' => 2, 'title' => 'Mrs', 'fname' => 'Betty', 'lname' => 'Smith'],
        ['id' => 3, 'title' => 'Ms', 'fname' => 'Jenny', 'lname' => 'Gumpert'],
        ['id' => 4, 'title' => 'Miss', 'fname' => 'Liz', 'lname' => 'McGuire'],
        ['id' => 5, 'title' => 'Mr', 'fname' => 'Terry', 'lname' => 'Fawlty'],
    ];

    public function setUp()
    {
        $this->collection = new Collection(
            new UnitOfWorkDouble(),
            new EntityBuilderDouble(),
            EntityDouble::class
        );

        $this->collection->setRowset(function() {
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

        $this->assertEquals([0, 1, 2, 3, 4], $keys);
    }

    public function testCollectionReturnsOnlyEntityInstancesOnIteration()
    {
        $this->assertContainsOnly(EntityDouble::class, $this->collection);
    }

    public function testAddWillNotAcceptWrongEntityType()
    {
        $this->setExpectedException(Exception\InvalidArgumentException::class);

        $badEntity = $this->getMock(
            AbstractEntity::class,
            [],
            [],
            'BadEntity',
            false
        );

        $this->collection->add($badEntity);
    }

    public function testAddEntityToCollectionIncrementsCountByOne()
    {
        $entity = new EntityDouble();
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

        $actual = [];

        foreach ($this->collection as $entity) {
            $actual[] = $entity->getId();
        }

        $this->assertEquals([2, 3, 4, 5], $actual);
    }

    public function testCanIterateProperlyOverCollectionWhenEntityRemovedFromMiddle()
    {
        $this->collection->remove(1);

        $actual = [];

        foreach ($this->collection as $entity) {
            $actual[] = $entity->getId();
        }

        $this->assertEquals([1, 3, 4, 5], $actual);
    }

    public function testCanIterateProperlyOverCollectionWhenMultipleEntitiesRemovedFromMiddle()
    {
        $this->collection->remove(1);
        $this->collection->remove(2);
        $this->collection->remove(3);

        $actual = [];

        foreach ($this->collection as $entity) {
            $actual[] = $entity->getId();
        }

        $this->assertEquals([1, 5], $actual);
    }

    public function testCanIterateProperlyOverCollectionWhenEntityRemovedFromEnd()
    {
        $this->collection->remove(4);

        $actual = [];

        foreach ($this->collection as $entity) {
            $actual[] = $entity->getId();
        }

        $this->assertEquals([1, 2, 3, 4], $actual);
    }

    public function testCanIterateAndRemoveEntityFromBeginningSimultaneously()
    {
        $actual = [];

        foreach ($this->collection as $key => $entity) {

            if ($entity->getId() == 1) {
                $this->collection->remove($key);
            } else {
                $actual[] = $entity->getId();
            }
        }

        $this->assertEquals([2, 3, 4, 5], $actual);
    }

    public function testCanIterateAndRemoveEntityFromMiddleSimultaneously()
    {
        $actual = [];

        foreach ($this->collection as $key => $entity) {

            if ($entity->getId() == 2) {
                $this->collection->remove($key);
            } else {
                $actual[] = $entity->getId();
            }
        }

        $this->assertEquals([1, 3, 4, 5], $actual);
    }

    public function testCanIterateAndRemoveMultipleEntitiesFromMiddleSimultaneously()
    {
        $actual = [];

        foreach ($this->collection as $key => $entity) {

            if (in_array($entity->getId(), [2, 3, 4])) {
                $this->collection->remove($key);
            } else {
                $actual[] = $entity->getId();
            }
        }

        $this->assertEquals([1, 5], $actual);
    }

    public function testCanIterateAndRemoveEntityFromEndSimultaneously()
    {
        $actual = [];

        foreach ($this->collection as $key => $entity) {

            if ($entity->getId() == 5) {
                $this->collection->remove($key);
            } else {
                $actual[] = $entity->getId();
            }
        }

        $this->assertEquals([1, 2, 3, 4], $actual);
    }

    public function testFilteringReturnsCorrectEntities()
    {
        $filtered = $this->collection->filter(function($entity) {
            return $entity->getTitle() === 'Mr';
        });

        $actual = [];

        foreach ($filtered as $entity) {
            $actual[] = $entity->getId();
        }

        $this->assertInstanceOf(Collection::class, $filtered);
        $this->assertEquals([1, 5], $actual);
    }

    public function testSortingReturnsCorrectEntities()
    {
        $this->collection->sort(function(EntityDouble $a, EntityDouble $b) {
            return strnatcmp($a->getFname(), $b->getFname());
        });

        $this->assertEquals('Betty', $this->collection->first()->getFname());
        $this->assertEquals('Tom', $this->collection->last()->getFname());
    }

}
