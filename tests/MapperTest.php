<?php
namespace EntityManager\Test;

use EntityManager\IdentityMap;
use EntityManager\Test\Double\EntityBuilder;
use EntityManager\Test\Double\CollectionFactory;
use EntityManager\Mapper\Mapper;
use EntityManager\Test\Double\MapperStrategy;
use EntityManager\Test\Double\Entity;

class MapperTest extends \PHPUnit_Framework_TestCase
{

    protected $mapper;

    protected $mapperStrategy;

    protected $identityMap;

    protected $data = [
        ['id' => '1', 'title' => 'Mr', 'fname' => 'Tom', 'lname' => 'Jones'],
        ['id' => '2', 'title' => 'Mrs', 'fname' => 'Betty', 'lname' => 'Smith'],
        ['id' => '3', 'title' => 'Ms', 'fname' => 'Jenny', 'lname' => 'Gumpert'],
    ];

    public function setUp()
    {
        $this->identityMap = new IdentityMap();
        $this->mapperStrategy = new MapperStrategy();
        $this->mapperStrategy->data = $this->data;

        $this->mapper = new Mapper(
            $this->identityMap,
            new EntityBuilder(),
            new CollectionFactory(),
            $this->mapperStrategy
        );
    }

    public function testFindGetsFromIdentityMapWhenExists()
    {
        $id = 23;

        $entity = new Entity();
        $entity->setId($id);

        $this->identityMap->add($entity);
        $result = $this->mapper->find(get_class($entity), $id);

        $this->assertEquals($entity, $result);
    }

    public function testFindReturnsNullIfNotFound()
    {
        $this->mapperStrategy->data = [];

        $result = $this->mapper->find(null, null);

        $this->assertNull($result);
    }

    public function testFindReturnsAnEntityWhenFound()
    {
        $result = $this->mapper->find(null, null);

        $this->assertInstanceOf('EntityManager\\Test\\Double\\Entity', $result);
    }

    public function testFindOneReturnsNullIfNotFound()
    {
        $this->mapperStrategy->data = [];

        $result = $this->mapper->findOne(null, null);

        $this->assertNull($result);
    }

    public function testFindOneReturnsAnEntityWhenFound()
    {
        $result = $this->mapper->find(null, null);

        $this->assertInstanceOf('EntityManager\\Test\\Double\\Entity', $result);
    }

    public function testFindAllReturnsAnEmptyCollectionWhenNoneFound()
    {
        $this->mapperStrategy->data = [];

        $result = $this->mapper->findAll(null, null);

        $this->assertInstanceOf('EntityManager\\Collection\\Collection', $result);
        $this->assertEquals(0, count($result));
    }

    public function testFindAllReturnsCollectionOfEntitiesWhenFound()
    {
        $result = $this->mapper->findAll(null, null);

        $this->assertInstanceOf('EntityManager\\Collection\\Collection', $result);
        $this->assertEquals(count($this->data), count($result));
    }

    public function testInsertAddsEntityToIdentityMap()
    {
        $id = max(array_column($this->data, 'id')) + 1;

        $entity = new Entity();
        $this->mapper->insert($entity);

        $result = $this->identityMap->exists(get_class($entity), $id);
        $this->assertEquals($entity, $result);
    }

}
