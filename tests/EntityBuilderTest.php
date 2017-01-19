<?php
namespace Boxspaced\EntityManager\Test;

use Boxspaced\EntityManager\Entity\EntityBuilder;
use Boxspaced\EntityManager\IdentityMap;
use Boxspaced\EntityManager\Exception;
use Boxspaced\EntityManager\Mapper\Query;
use Boxspaced\EntityManager\Entity\AbstractEntity;

class EntityBuilderTest extends \PHPUnit_Framework_TestCase
{

    protected $identityMap;
    protected $mapperFactory;
    protected $config;

    public function setUp()
    {
        $this->identityMap = new IdentityMap();
        $this->mapperFactory = new MapperFactoryDouble();

        $this->config = [
            'types' => [
                EntityDouble::class => [
                    'mapper' => [
                        'strategy' => MapperStrategyDouble::class
                    ],
                    'entity' => [
                        'fields' => [
                            'id' => [
                                'type' => AbstractEntity::TYPE_INT,
                            ],
                            'title' => [
                                'type' => AbstractEntity::TYPE_STRING,
                            ],
                            'fname' => [
                                'type' => AbstractEntity::TYPE_STRING,
                            ],
                            'lname' => [
                                'type' => AbstractEntity::TYPE_STRING,
                            ],
                            'parent' => [
                                'type' => EntityDouble::class,
                            ],
                        ],
                        'one_to_many' => [
                            'children' => [
                                'type' => EntityDouble::class,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function createBuilder()
    {
        return new EntityBuilder(
            $this->identityMap,
            new UnitOfWorkDouble(),
            new EntityFactoryDouble($this->config),
            $this->mapperFactory,
            $this->config
        );
    }

    public function testBuildGetsFromIdentityMapWhenExists()
    {
        $id = 3;

        $entity = new EntityDouble();
        $entity->setId($id);
        $this->identityMap->add($entity);

        $data = ['id' => $id, 'title' => 'Ms', 'fname' => 'Jenny', 'lname' => 'Gumpert'];
        $result = $this->createBuilder()->build(get_class($entity), $data);

        $this->assertEquals($entity, $result);
    }

    public function testBuildReturnsNewEntityWhenNotInIdentityMap()
    {
        $data = ['id' => 49, 'title' => 'Ms', 'fname' => 'Jenny', 'lname' => 'Gumpert'];
        $result = $this->createBuilder()->build(EntityDouble::class, $data);

        $this->assertInstanceOf(EntityDouble::class, $result);
    }

    public function testBuildAddsToIdentityMapWhenRowFromPersistantStorage()
    {
        $data = ['id' => 33, 'title' => 'Ms', 'fname' => 'Jenny', 'lname' => 'Gumpert'];
        $entity = $this->createBuilder()->build(EntityDouble::class, $data);

        $result = $this->identityMap->exists(get_class($entity), $entity->getId());

        $this->assertEquals($entity, $result);
    }

    public function testBuildWillNotAcceptEmptyRow()
    {
        $this->setExpectedException(Exception\UnexpectedValueException::class);

        $data = [];
        $this->createBuilder()->build(EntityDouble::class, $data);
    }

    public function testBuildWillNotAcceptRowWithoutId()
    {
        $this->setExpectedException(Exception\UnexpectedValueException::class);

        $data = ['title' => 'Ms', 'fname' => 'Jenny', 'lname' => 'Gumpert'];
        $this->createBuilder()->build(EntityDouble::class, $data);
    }

    public function testBuildCreatesCorrectQueryForOneToMany()
    {
        $id = 33;

        $data = ['id' => $id, 'title' => 'Ms', 'fname' => 'Jenny', 'lname' => 'Gumpert'];
        $this->createBuilder()->build(EntityDouble::class, $data);

        $query = $this->mapperFactory->mapper->query;

        $this->assertInstanceOf(Query::class, $query);

        $field = $query->getFields()[0];

        $this->assertEquals('parent.id', $field->getName());
        $this->assertEquals('=', $field->getOperator());
        $this->assertEquals($id, $field->getValue());
    }

}
