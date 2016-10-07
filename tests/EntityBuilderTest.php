<?php
namespace EntityManager\Test;

use EntityManager\Entity\Builder as EntityBuilder;
use EntityManager\Test\Double\Entity;
use EntityManager\IdentityMap;
use EntityManager\Test\Double\UnitOfWork;
use EntityManager\Test\Double\EntityFactory;
use EntityManager\Test\Double\MapperFactory;
use Zend\Config\Config;

class EntityBuilderTest extends \PHPUnit_Framework_TestCase
{

    protected $identityMap;

    protected $builder;

    public function setUp()
    {
        $this->identityMap = new IdentityMap();

        $this->builder = new EntityBuilder(
            $this->identityMap,
            new UnitOfWork(),
            new EntityFactory(),
            new MapperFactory(),
            new Config(require 'files/config.php')
        );
    }

    public function testBuildGetsFromIdentityMapWhenExists()
    {
        $id = 3;

        $entity = new Entity();
        $entity->setId($id);
        $this->identityMap->add($entity);

        $data = ['id' => $id, 'title' => 'Ms', 'fname' => 'Jenny', 'lname' => 'Gumpert'];
        $result = $this->builder->build(get_class($entity), $data);

        $this->assertEquals($entity, $result);
    }

    public function testBuildReturnsNewEntityWhenNotInIdentityMap()
    {
        $data = ['id' => 49, 'title' => 'Ms', 'fname' => 'Jenny', 'lname' => 'Gumpert'];
        $result = $this->builder->build('EntityManager\\Test\\Double\\Entity', $data);

        $this->assertInstanceOf('EntityManager\\Test\\Double\\Entity', $result);
    }

    public function testBuildAddsToIdentityMapWhenRowFromPersistantStorage()
    {
        $data = ['id' => 33, 'title' => 'Ms', 'fname' => 'Jenny', 'lname' => 'Gumpert'];
        $entity = $this->builder->build('EntityManager\\Test\\Double\\Entity', $data);

        $result = $this->identityMap->exists(get_class($entity), $entity->getId());

        $this->assertEquals($entity, $result);
    }

    public function testBuildWillNotAcceptEmptyRow()
    {
        $this->setExpectedException('UnexpectedValueException');

        $data = [];
        $this->builder->build('EntityManager\\Test\\Double\\Entity', $data);
    }

    public function testBuildWillNotAcceptRowWithoutId()
    {
        $this->setExpectedException('UnexpectedValueException');

        $data = ['title' => 'Ms', 'fname' => 'Jenny', 'lname' => 'Gumpert'];
        $this->builder->build('EntityManager\\Test\\Double\\Entity', $data);
    }

}
