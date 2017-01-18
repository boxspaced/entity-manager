<?php
namespace Boxspaced\EntityManager\Test;

use Boxspaced\EntityManager\Entity\EntityBuilder;
use Boxspaced\EntityManager\IdentityMap;
use Boxspaced\EntityManager\Exception;
use Boxspaced\EntityManager\Mapper\Conditions;
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

    public function testBuildUsesConditionsInvokableFactoryForOneToMany()
    {
        $this->config['types'][EntityDouble::class]['entity']['one_to_many']['children'] = [
            'type' => EntityDouble::class,
            'conditions' => [
                'factory' => ConditionsFactoryDouble::class,
                'options' => [],
            ],
        ];

        $data = ['id' => 33, 'title' => 'Ms', 'fname' => 'Jenny', 'lname' => 'Gumpert'];
        $this->createBuilder()->build(EntityDouble::class, $data);

        $this->assertInstanceOf(Conditions::class, $this->mapperFactory->mapper->conditions);
    }

    public function testBuildUsesConditionsClosureFactoryForOneToMany()
    {
        $this->config['types'][EntityDouble::class]['entity']['one_to_many']['children'] = [
            'type' => EntityDouble::class,
            'conditions' => function($id) {
                return new Conditions();
            },
        ];

        $data = ['id' => 33, 'title' => 'Ms', 'fname' => 'Jenny', 'lname' => 'Gumpert'];
        $this->createBuilder()->build(EntityDouble::class, $data);

        $this->assertInstanceOf(Conditions::class, $this->mapperFactory->mapper->conditions);
    }

}
