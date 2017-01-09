<?php
namespace Boxspaced\EntityManager\Test;

use Boxspaced\EntityManager\IdentityMap;
use Boxspaced\EntityManager\Mapper\Mapper;
use Boxspaced\EntityManager\Collection\Collection;

class MapperTest extends \PHPUnit_Framework_TestCase
{

    protected $mapper;

    protected $mapperStrategy;

    protected $identityMap;

    protected $data = [
        ['id' => 1, 'title' => 'Mr', 'fname' => 'Tom', 'lname' => 'Jones'],
        ['id' => 2, 'title' => 'Mrs', 'fname' => 'Betty', 'lname' => 'Smith'],
        ['id' => 3, 'title' => 'Ms', 'fname' => 'Jenny', 'lname' => 'Gumpert'],
    ];

    public function setUp()
    {
        $this->identityMap = new IdentityMap();
        $this->mapperStrategy = new MapperStrategyDouble();
        $this->mapperStrategy->data = $this->data;

        $this->mapper = new Mapper(
            $this->identityMap,
            new EntityBuilderDouble(),
            new CollectionFactoryDouble(),
            $this->mapperStrategy
        );
    }

    public function testFindGetsFromIdentityMapWhenExists()
    {
        $id = 23;

        $entity = new EntityDouble();
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

        $this->assertInstanceOf(EntityDouble::class, $result);
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

        $this->assertInstanceOf(EntityDouble::class, $result);
    }

    public function testFindAllReturnsAnEmptyCollectionWhenNoneFound()
    {
        $this->mapperStrategy->data = [];

        $result = $this->mapper->findAll(null, null);

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEquals(0, count($result));
    }

    public function testFindAllReturnsCollectionOfEntitiesWhenFound()
    {
        $result = $this->mapper->findAll(null, null);

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEquals(count($this->data), count($result));
    }

    public function testInsertAddsEntityToIdentityMap()
    {
        $id = max(array_column($this->data, 'id')) + 1;

        $entity = new EntityDouble();
        $this->mapper->insert($entity);

        $result = $this->identityMap->exists(get_class($entity), $id);
        $this->assertEquals($entity, $result);
    }

}
