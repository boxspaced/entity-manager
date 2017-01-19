<?php
namespace Boxspaced\EntityManager\Test;

use Boxspaced\EntityManager\Mapper\Select;
use Boxspaced\EntityManager\Mapper\Query;
use Boxspaced\EntityManager\Exception;
use Boxspaced\EntityManager\Entity\AbstractEntity;

class SelectTest extends \PHPUnit_Framework_TestCase
{

    protected $config;

    public function setUp()
    {
        $this->config = [
            'types' => [
                'Item' => [
                    'mapper' => [
                        'params' => [
                            'table' => 'item',
                            'columns' => [
                                'id' => 'i_id',
                                'desc' => 'description',
                                'longDesc' => 'long_description',
                                'author' => 'author_id',
                                'publisher' => 'publisher_id',
                                'status' => 'status_id',
                                'versionOf' => 'version_of_id',
                            ],
                        ],
                    ],
                    'entity' => [
                        'fields' => [
                            'id' => [
                                'type' => AbstractEntity::TYPE_INT,
                            ],
                            'desc' => [
                                'type' => AbstractEntity::TYPE_STRING,
                            ],
                            'longDesc' => [
                                'type' => AbstractEntity::TYPE_STRING,
                            ],
                            'author' => [
                                'type' => 'User',
                            ],
                            'publisher' => [
                                'type' => 'User',
                            ],
                            'status' => [
                                'type' => 'ContentStatus',
                            ],
                            'versionOf' => [
                                'type' => 'Item',
                            ],
                        ],
                    ],
                ],
                'User' => [
                    'mapper' => [
                        'params' => [
                            'table' => 'user',
                            'columns' => [
                                'id' => 'u_id',
                                'desc' => 'description',
                                'longDesc' => 'long_description',
                                'type' => 'type_id',
                            ],
                        ],
                    ],
                    'entity' => [
                        'fields' => [
                            'id' => [
                                'type' => AbstractEntity::TYPE_INT,
                            ],
                            'desc' => [
                                'type' => AbstractEntity::TYPE_STRING,
                            ],
                            'longDesc' => [
                                'type' => AbstractEntity::TYPE_STRING,
                            ],
                            'type' => [
                                'type' => 'UserType',
                            ],
                        ],
                    ],
                ],
                'UserType' => [
                    'mapper' => [
                        'params' => [
                            'table' => 'user_type',
                            'columns' => [
                                'id' => 'ut_id',
                                'desc' => 'description',
                                'longDesc' => 'long_description',
                            ],
                        ],
                    ],
                    'entity' => [
                        'fields' => [
                            'id' => [
                                'type' => AbstractEntity::TYPE_INT,
                            ],
                            'desc' => [
                                'type' => AbstractEntity::TYPE_STRING,
                            ],
                            'longDesc' => [
                                'type' => AbstractEntity::TYPE_STRING,
                            ],
                        ]
                    ]
                ],
                'ContentStatus' => [
                    'mapper' => [
                        'params' => [
                            'table' => 'content_status',
                            'columns' => [
                                'id' => 'cs_id',
                                'desc' => 'description',
                                'longDesc' => 'long_description',
                                'type' => 'type_id',
                            ],
                        ],
                    ],
                    'entity' => [
                        'fields' => [
                            'id' => [
                                'type' => AbstractEntity::TYPE_INT,
                            ],
                            'desc' => [
                                'type' => AbstractEntity::TYPE_STRING,
                            ],
                            'longDesc' => [
                                'type' => AbstractEntity::TYPE_STRING,
                            ],
                            'type' => [
                                'type' => 'ContentStatusType',
                            ],
                        ],
                    ],
                ],
                'ContentStatusType' => [
                    'mapper' => [
                        'params' => [
                            'table' => 'content_status_type',
                            'columns' => [
                                'id' => 'cst_id',
                                'desc' => 'description',
                                'longDesc' => 'long_description',
                            ],
                        ],
                    ],
                    'entity' => [
                        'fields' => [
                            'id' => [
                                'type' => AbstractEntity::TYPE_INT,
                            ],
                            'desc' => [
                                'type' => AbstractEntity::TYPE_STRING,
                            ],
                            'longDesc' => [
                                'type' => AbstractEntity::TYPE_STRING,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    protected function getSqlFromQuery(Query $query)
    {
        $select = new Select($this->config, 'Item', $query);
        return @$select->getSqlString();
    }

    public function testIncorrectForeignFieldConditionThrowsException()
    {
        $this->setExpectedException(Exception\UnexpectedValueException::class);

        $query = new Query();
        $query->field('notInMap.id')->eq(5);

        $this->getSqlFromQuery($query);
    }

    public function testIncorrectDeepForeignFieldConditionThrowsException()
    {
        $this->setExpectedException(Exception\UnexpectedValueException::class);

        $query = new Query();
        $query->field('item.notInMap.id')->eq(5);

        $this->getSqlFromQuery($query);
    }

    public function testApplySingleFieldCondition()
    {
        $query = new Query();
        $query->field('name')->eq('test-page');

        $sql = $this->getSqlFromQuery($query);

        $expected = 'SELECT "item".* FROM "item" WHERE item.name = \'test-page\'';
        $this->assertEquals($expected, $sql);
    }

    public function testApplyForeignFieldConditionDirectlyToForiegnKeyColumn()
    {
        $query = new Query();
        $query->field('author')->isNull();
        $query->field('author')->eq(8);

        $sql = $this->getSqlFromQuery($query);

        $expected = 'SELECT "item".* FROM "item" WHERE item.author_id IS NULL AND item.author_id = \'8\'';
        $this->assertEquals($expected, $sql);
    }

    public function testApplyDeepForeignFieldConditionDirectlyToForiegnKeyColumn()
    {
        $query = new Query();
        $query->field('author.type')->isNull();
        $query->field('status.type')->eq(8);

        $sql = $this->getSqlFromQuery($query);

        $expected = 'SELECT "item".* FROM "item" '
                  . 'INNER JOIN "user" AS "author" ON "item"."author_id" = "author"."u_id" '
                  . 'INNER JOIN "content_status" AS "status" ON "item"."status_id" = "status"."cs_id" '
                  . 'WHERE author.type_id IS NULL AND status.type_id = \'8\'';
        $this->assertEquals($expected, $sql);
    }

    public function testApplyMultipleFieldQuery()
    {
        $query = new Query();
        $query->field('name')->eq('test-page');
        $query->field('archived')->eq('1');

        $sql = $this->getSqlFromQuery($query);

        $expected = 'SELECT "item".* FROM "item" WHERE item.name = \'test-page\' AND item.archived = \'1\'';
        $this->assertEquals($expected, $sql);
    }

    public function testApplyMultipleUnmappedCamelCasedFieldNames()
    {
        $query = new Query();
        $query->field('nameCamelCase')->eq('test-page');
        $query->field('archivedCamelCase')->eq('1');

        $sql = $this->getSqlFromQuery($query);

        $expected = 'SELECT "item".* FROM "item" WHERE item.nameCamelCase = \'test-page\' AND item.archivedCamelCase = \'1\'';
        $this->assertEquals($expected, $sql);
    }

    public function testApplySingleForeignFieldCondition()
    {
        $query = new Query();
        $query->field('author.username')->eq('jbloggs');

        $sql = $this->getSqlFromQuery($query);

        $expected = 'SELECT "item".* FROM "item" '
                  . 'INNER JOIN "user" AS "author" ON "item"."author_id" = "author"."u_id" '
                  . 'WHERE author.username = \'jbloggs\'';
        $this->assertEquals($expected, $sql);
    }

    public function testApplyMultipleForeignFieldQuery()
    {
        $query = new Query();
        $query->field('author.username')->eq('jbloggs');
        $query->field('status.name')->eq('published');

        $sql = $this->getSqlFromQuery($query);

        $expected = 'SELECT "item".* FROM "item" '
                  . 'INNER JOIN "user" AS "author" ON "item"."author_id" = "author"."u_id" '
                  . 'INNER JOIN "content_status" AS "status" ON "item"."status_id" = "status"."cs_id" '
                  . 'WHERE author.username = \'jbloggs\' AND status.name = \'published\'';
        $this->assertEquals($expected, $sql);
    }

    public function testApplySingleDeepForeignFieldCondition()
    {
        $query = new Query();
        $query->field('author.type.name')->eq('admin');

        $sql = $this->getSqlFromQuery($query);

        $expected = 'SELECT "item".* FROM "item" '
                  . 'INNER JOIN "user" AS "author" ON "item"."author_id" = "author"."u_id" '
                  . 'INNER JOIN "user_type" AS "author_type" ON "author"."type_id" = "author_type"."ut_id" '
                  . 'WHERE author_type.name = \'admin\'';
        $this->assertEquals($expected, $sql);
    }

    public function testApplyMultipleDeepForeignFieldQuery()
    {
        $query = new Query();
        $query->field('author.type.active')->eq('1');
        $query->field('status.type.name')->eq('global');

        $sql = $this->getSqlFromQuery($query);

        $expected = 'SELECT "item".* FROM "item" '
                  . 'INNER JOIN "user" AS "author" ON "item"."author_id" = "author"."u_id" '
                  . 'INNER JOIN "user_type" AS "author_type" ON "author"."type_id" = "author_type"."ut_id" '
                  . 'INNER JOIN "content_status" AS "status" ON "item"."status_id" = "status"."cs_id" '
                  . 'INNER JOIN "content_status_type" AS "status_type" ON "status"."type_id" = "status_type"."cst_id" '
                  . 'WHERE author_type.active = \'1\' AND status_type.name = \'global\'';
        $this->assertEquals($expected, $sql);
    }

    public function testApplySingleOrderCondition()
    {
        $query = new Query();
        $query->order('name', 'ASC');

        $sql = $this->getSqlFromQuery($query);

        $expected = 'SELECT "item".* FROM "item" ORDER BY "item"."name" ASC';
        $this->assertEquals($expected, $sql);
    }

    public function testApplyMultipleOrderQuery()
    {
        $query = new Query();
        $query->order('name', 'ASC');
        $query->order('date', 'DESC');

        $sql = $this->getSqlFromQuery($query);

        $expected = 'SELECT "item".* FROM "item" ORDER BY "item"."name" ASC, "item"."date" DESC';
        $this->assertEquals($expected, $sql);
    }

    public function testApplySingleForeignOrderCondition()
    {
        $query = new Query();
        $query->order('author.username', 'ASC');

        $sql = $this->getSqlFromQuery($query);

        $expected = 'SELECT "item".* FROM "item" '
                  . 'INNER JOIN "user" AS "author" ON "item"."author_id" = "author"."u_id" '
                  . 'ORDER BY "author"."username" ASC';
        $this->assertEquals($expected, $sql);
    }

    public function testApplyMultipleForeignOrderQuery()
    {
        $query = new Query();
        $query->order('author.username', 'ASC');
        $query->order('status.name', 'DESC');

        $sql = $this->getSqlFromQuery($query);

        $expected = 'SELECT "item".* FROM "item" '
                  . 'INNER JOIN "user" AS "author" ON "item"."author_id" = "author"."u_id" '
                  . 'INNER JOIN "content_status" AS "status" ON "item"."status_id" = "status"."cs_id" '
                  . 'ORDER BY "author"."username" ASC, "status"."name" DESC';
        $this->assertEquals($expected, $sql);
    }

    public function testApplyPagingCondition()
    {
        $query = new Query();
        $query->paging(10, 10);

        $sql = $this->getSqlFromQuery($query);

        $expected = 'SELECT "item".* FROM "item" LIMIT \'10\' OFFSET \'10\'';
        $this->assertEquals($expected, $sql);
    }

    public function testApplyMappedColumnCondtions()
    {
        $query = new Query();
        $query->field('desc')->eq('testing');
        $query->field('longDesc')->eq('testing testing');

        $sql = $this->getSqlFromQuery($query);

        $expected = 'SELECT "item".* FROM "item" WHERE '
                  . 'item.description = \'testing\' AND item.long_description = \'testing testing\'';
        $this->assertEquals($expected, $sql);
    }

    public function testApplyForeignFieldMappedColumnQuery()
    {
        $query = new Query();
        $query->field('author.desc')->eq('testing');
        $query->field('status.longDesc')->eq('testing testing');

        $sql = $this->getSqlFromQuery($query);

        $expected = 'SELECT "item".* FROM "item" '
                  . 'INNER JOIN "user" AS "author" ON "item"."author_id" = "author"."u_id" '
                  . 'INNER JOIN "content_status" AS "status" ON "item"."status_id" = "status"."cs_id" '
                  . 'WHERE author.description = \'testing\' '
                  . 'AND status.long_description = \'testing testing\'';
        $this->assertEquals($expected, $sql);
    }

    public function testApplyDeepForeignFieldMappedColumnQuery()
    {
        $query = new Query();
        $query->field('author.type.desc')->eq('testing');
        $query->field('status.type.longDesc')->eq('testing testing');

        $sql = $this->getSqlFromQuery($query);

        $expected = 'SELECT "item".* FROM "item" '
                  . 'INNER JOIN "user" AS "author" ON "item"."author_id" = "author"."u_id" '
                  . 'INNER JOIN "user_type" AS "author_type" ON "author"."type_id" = "author_type"."ut_id" '
                  . 'INNER JOIN "content_status" AS "status" ON "item"."status_id" = "status"."cs_id" '
                  . 'INNER JOIN "content_status_type" AS "status_type" ON "status"."type_id" = "status_type"."cst_id" '
                  . 'WHERE author_type.description = \'testing\' '
                  . 'AND status_type.long_description = \'testing testing\'';
        $this->assertEquals($expected, $sql);
    }

    public function testApplyForeignFieldMappedColumnOrderQuery()
    {
        $query = new Query();
        $query->order('author.desc', 'ASC');
        $query->order('status.longDesc', 'DESC');

        $sql = $this->getSqlFromQuery($query);

        $expected = 'SELECT "item".* FROM "item" '
                  . 'INNER JOIN "user" AS "author" ON "item"."author_id" = "author"."u_id" '
                  . 'INNER JOIN "content_status" AS "status" ON "item"."status_id" = "status"."cs_id" '
                  . 'ORDER BY "author"."description" ASC, "status"."long_description" DESC';
        $this->assertEquals($expected, $sql);
    }

    public function testApplyMultipleForeignFieldQueryOfSameType()
    {
        $query = new Query();
        $query->field('author.username')->eq('jbloggs');
        $query->field('publisher.username')->eq('jbloggs');

        $sql = $this->getSqlFromQuery($query);

        $expected = 'SELECT "item".* FROM "item" '
                  . 'INNER JOIN "user" AS "author" ON "item"."author_id" = "author"."u_id" '
                  . 'INNER JOIN "user" AS "publisher" ON "item"."publisher_id" = "publisher"."u_id" '
                  . 'WHERE author.username = \'jbloggs\' AND publisher.username = \'jbloggs\'';
        $this->assertEquals($expected, $sql);
    }

    public function testApplyMultipleDeepForeignFieldQueryOfSameType()
    {
        $query = new Query();
        $query->field('author.type.active')->eq('1');
        $query->field('publisher.type.active')->eq('1');

        $sql = $this->getSqlFromQuery($query);

        $expected = 'SELECT "item".* FROM "item" '
                  . 'INNER JOIN "user" AS "author" ON "item"."author_id" = "author"."u_id" '
                  . 'INNER JOIN "user_type" AS "author_type" ON "author"."type_id" = "author_type"."ut_id" '
                  . 'INNER JOIN "user" AS "publisher" ON "item"."publisher_id" = "publisher"."u_id" '
                  . 'INNER JOIN "user_type" AS "publisher_type" ON "publisher"."type_id" = "publisher_type"."ut_id" '
                  . 'WHERE author_type.active = \'1\' AND publisher_type.active = \'1\'';
        $this->assertEquals($expected, $sql);
    }

    public function testApplySingleForeignFieldConditionReferencingSameTypeAsSelf()
    {
        $query = new Query();
        $query->field('versionOf')->eq(4);

        $sql = $this->getSqlFromQuery($query);

        $expected = 'SELECT "item".* FROM "item" WHERE item.version_of_id = \'4\'';
        $this->assertEquals($expected, $sql);
    }

    public function testApplyAllQuery()
    {
        $query = new Query();
        $query->field('field1')->eq('value1');
        $query->field('field2')->notEq('value2');
        $query->field('field3')->isNull();
        $query->field('field4')->isNotNull();
        $query->field('field5')->gt(5);
        $query->field('field6')->lt(6);
        $query->field('author.username')->eq('jbloggs');
        $query->field('status.name')->eq('published');
        $query->field('author.type.active')->eq('1');
        $query->field('status.type.name')->eq('global');
        $query->field('desc')->eq('testing');
        $query->field('longDesc')->eq('testing testing');
        $query->field('unmappedCamelCase')->eq('testing');
        $query->field('author.desc')->eq('testing');
        $query->field('status.longDesc')->eq('testing testing');
        $query->field('author.type.desc')->eq('testing');
        $query->field('status.type.longDesc')->eq('testing testing');
        $query->field('author')->isNull();
        $query->field('status.Type')->eq(8);
        $query->field('publisher.username')->eq('jbloggs');
        $query->field('publisher.type.active')->eq('1');
        $query->order('name', 'ASC');
        $query->order('date', 'DESC');
        $query->order('author.username', 'ASC');
        $query->order('status.name', 'DESC');
        $query->order('author.desc', 'ASC');
        $query->order('status.longDesc', 'DESC');
        $query->order('author', 'ASC');
        $query->order('status.type', 'DESC');
        $query->order('publisher.username', 'ASC');
        $query->paging(84, 12);

        $sql = $this->getSqlFromQuery($query);

        $expected = 'SELECT "item".* FROM "item" '
                  . 'INNER JOIN "user" AS "author" ON "item"."author_id" = "author"."u_id" '
                  . 'INNER JOIN "content_status" AS "status" ON "item"."status_id" = "status"."cs_id" '
                  . 'INNER JOIN "user_type" AS "author_type" ON "author"."type_id" = "author_type"."ut_id" '
                  . 'INNER JOIN "content_status_type" AS "status_type" ON "status"."type_id" = "status_type"."cst_id" '
                  . 'INNER JOIN "user" AS "publisher" ON "item"."publisher_id" = "publisher"."u_id" '
                  . 'INNER JOIN "user_type" AS "publisher_type" ON "publisher"."type_id" = "publisher_type"."ut_id" '
                  . 'WHERE item.field1 = \'value1\' AND item.field2 != \'value2\' '
                  . 'AND item.field3 IS NULL AND item.field4 IS NOT NULL AND item.field5 > \'5\' AND item.field6 < \'6\' '
                  . 'AND author.username = \'jbloggs\' AND status.name = \'published\' '
                  . 'AND author_type.active = \'1\' AND status_type.name = \'global\' '
                  . 'AND item.description = \'testing\' AND item.long_description = \'testing testing\' '
                  . 'AND item.unmappedCamelCase = \'testing\' AND author.description = \'testing\' '
                  . 'AND status.long_description = \'testing testing\' '
                  . 'AND author_type.description = \'testing\' AND status_type.long_description = \'testing testing\' '
                  . 'AND item.author_id IS NULL AND status.type_id = \'8\' '
                  . 'AND publisher.username = \'jbloggs\' AND publisher_type.active = \'1\' '
                  . 'ORDER BY "item"."name" ASC, "item"."date" DESC, "author"."username" ASC, "status"."name" DESC, '
                  . '"author"."description" ASC, "status"."long_description" DESC, "item"."author_id" ASC, '
                  . '"status"."type_id" DESC, "publisher"."username" ASC LIMIT \'12\' OFFSET \'84\'';
        $this->assertEquals($expected, $sql);
    }

}
