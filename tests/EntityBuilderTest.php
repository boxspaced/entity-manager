<?php
namespace Boxspaced\EntityManager\Test;

use Boxspaced\EntityManager\Entity\EntityBuilder;
use Boxspaced\EntityManager\IdentityMap;
use Boxspaced\EntityManager\Exception;

class EntityBuilderTest extends \PHPUnit_Framework_TestCase
{

    protected $identityMap;

    protected $builder;

    public function setUp()
    {
        $this->identityMap = new IdentityMap();

        $this->builder = new EntityBuilder(
            $this->identityMap,
            new UnitOfWorkDouble(),
            new EntityFactoryDouble(),
            new MapperFactoryDouble(),
            require 'config.php'
        );
    }

    public function testBuildGetsFromIdentityMapWhenExists()
    {
        $id = 3;

        $entity = new EntityDouble();
        $entity->setId($id);
        $this->identityMap->add($entity);

        $data = ['id' => $id, 'title' => 'Ms', 'fname' => 'Jenny', 'lname' => 'Gumpert'];
        $result = $this->builder->build(get_class($entity), $data);

        $this->assertEquals($entity, $result);
    }

    public function testBuildReturnsNewEntityWhenNotInIdentityMap()
    {
        $data = ['id' => 49, 'title' => 'Ms', 'fname' => 'Jenny', 'lname' => 'Gumpert'];
        $result = $this->builder->build(EntityDouble::class, $data);

        $this->assertInstanceOf(EntityDouble::class, $result);
    }

    public function testBuildAddsToIdentityMapWhenRowFromPersistantStorage()
    {
        $data = ['id' => 33, 'title' => 'Ms', 'fname' => 'Jenny', 'lname' => 'Gumpert'];
        $entity = $this->builder->build(EntityDouble::class, $data);

        $result = $this->identityMap->exists(get_class($entity), $entity->getId());

        $this->assertEquals($entity, $result);
    }

    public function testBuildWillNotAcceptEmptyRow()
    {
        $this->setExpectedException(Exception\UnexpectedValueException::class);

        $data = [];
        $this->builder->build(EntityDouble::class, $data);
    }

    public function testBuildWillNotAcceptRowWithoutId()
    {
        $this->setExpectedException(Exception\UnexpectedValueException::class);

        $data = ['title' => 'Ms', 'fname' => 'Jenny', 'lname' => 'Gumpert'];
        $this->builder->build(EntityDouble::class, $data);
    }

}
