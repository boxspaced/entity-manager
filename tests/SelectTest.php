<?php
namespace Boxspaced\EntityManager\Test;

use Zend\Config\Config;
use Boxspaced\EntityManager\Mapper\Sql\Select;
use Boxspaced\EntityManager\Mapper\Conditions\Conditions;

class SelectTest extends \PHPUnit_Framework_TestCase
{

    protected $config;

    public function setUp()
    {
        $this->config = new Config(require 'files/config.php');
    }

    protected function getSqlFromConditions(Conditions $conditions)
    {
        $select = new Select($this->config, 'Item', $conditions);
        return @$select->getSqlString();
    }

    public function testIncorrectForeignFieldConditionThrowsException()
    {
        $this->setExpectedException('UnexpectedValueException');

        $conditions = new Conditions();
        $conditions->field('notInMap.id')->eq(5);

        $this->getSqlFromConditions($conditions);
    }

    public function testIncorrectDeepForeignFieldConditionThrowsException()
    {
        $this->setExpectedException('UnexpectedValueException');

        $conditions = new Conditions();
        $conditions->field('item.notInMap.id')->eq(5);

        $this->getSqlFromConditions($conditions);
    }

    public function testApplySingleFieldCondition()
    {
        $conditions = new Conditions();
        $conditions->field('name')->eq('test-page');

        $sql = $this->getSqlFromConditions($conditions);

        $expected = 'SELECT "item".* FROM "item" WHERE item.name = \'test-page\'';
        $this->assertEquals($expected, $sql);
    }

    public function testApplyForeignFieldConditionDirectlyToForiegnKeyColumn()
    {
        $conditions = new Conditions();
        $conditions->field('author')->isNull();
        $conditions->field('author')->eq(8);

        $sql = $this->getSqlFromConditions($conditions);

        $expected = 'SELECT "item".* FROM "item" WHERE item.author_id IS NULL AND item.author_id = \'8\'';
        $this->assertEquals($expected, $sql);
    }

    public function testApplyDeepForeignFieldConditionDirectlyToForiegnKeyColumn()
    {
        $conditions = new Conditions();
        $conditions->field('author.type')->isNull();
        $conditions->field('status.type')->eq(8);

        $sql = $this->getSqlFromConditions($conditions);

        $expected = 'SELECT "item".* FROM "item" '
                  . 'INNER JOIN "user" AS "author" ON "item"."author_id" = "author"."u_id" '
                  . 'INNER JOIN "content_status" AS "status" ON "item"."status_id" = "status"."cs_id" '
                  . 'WHERE author.type_id IS NULL AND status.type_id = \'8\'';
        $this->assertEquals($expected, $sql);
    }

    public function testApplyMultipleFieldConditions()
    {
        $conditions = new Conditions();
        $conditions->field('name')->eq('test-page');
        $conditions->field('archived')->eq('1');

        $sql = $this->getSqlFromConditions($conditions);

        $expected = 'SELECT "item".* FROM "item" WHERE item.name = \'test-page\' AND item.archived = \'1\'';
        $this->assertEquals($expected, $sql);
    }

    public function testApplyMultipleUnmappedCamelCasedFieldNames()
    {
        $conditions = new Conditions();
        $conditions->field('nameCamelCase')->eq('test-page');
        $conditions->field('archivedCamelCase')->eq('1');

        $sql = $this->getSqlFromConditions($conditions);

        $expected = 'SELECT "item".* FROM "item" WHERE item.name_camel_case = \'test-page\' AND item.archived_camel_case = \'1\'';
        $this->assertEquals($expected, $sql);
    }

    public function testApplySingleForeignFieldCondition()
    {
        $conditions = new Conditions();
        $conditions->field('author.username')->eq('rwallwork');

        $sql = $this->getSqlFromConditions($conditions);

        $expected = 'SELECT "item".* FROM "item" '
                  . 'INNER JOIN "user" AS "author" ON "item"."author_id" = "author"."u_id" '
                  . 'WHERE author.username = \'rwallwork\'';
        $this->assertEquals($expected, $sql);
    }

    public function testApplyMultipleForeignFieldConditions()
    {
        $conditions = new Conditions();
        $conditions->field('author.username')->eq('rwallwork');
        $conditions->field('status.name')->eq('published');

        $sql = $this->getSqlFromConditions($conditions);

        $expected = 'SELECT "item".* FROM "item" '
                  . 'INNER JOIN "user" AS "author" ON "item"."author_id" = "author"."u_id" '
                  . 'INNER JOIN "content_status" AS "status" ON "item"."status_id" = "status"."cs_id" '
                  . 'WHERE author.username = \'rwallwork\' AND status.name = \'published\'';
        $this->assertEquals($expected, $sql);
    }

    public function testApplySingleDeepForeignFieldCondition()
    {
        $conditions = new Conditions();
        $conditions->field('author.type.name')->eq('admin');

        $sql = $this->getSqlFromConditions($conditions);

        $expected = 'SELECT "item".* FROM "item" '
                  . 'INNER JOIN "user" AS "author" ON "item"."author_id" = "author"."u_id" '
                  . 'INNER JOIN "user_type" AS "author_type" ON "author"."type_id" = "author_type"."ut_id" '
                  . 'WHERE author_type.name = \'admin\'';
        $this->assertEquals($expected, $sql);
    }

    public function testApplyMultipleDeepForeignFieldConditions()
    {
        $conditions = new Conditions();
        $conditions->field('author.type.active')->eq('1');
        $conditions->field('status.type.name')->eq('global');

        $sql = $this->getSqlFromConditions($conditions);

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
        $conditions = new Conditions();
        $conditions->order('name', 'ASC');

        $sql = $this->getSqlFromConditions($conditions);

        $expected = 'SELECT "item".* FROM "item" ORDER BY "item"."name" ASC';
        $this->assertEquals($expected, $sql);
    }

    public function testApplyMultipleOrderConditions()
    {
        $conditions = new Conditions();
        $conditions->order('name', 'ASC');
        $conditions->order('date', 'DESC');

        $sql = $this->getSqlFromConditions($conditions);

        $expected = 'SELECT "item".* FROM "item" ORDER BY "item"."name" ASC, "item"."date" DESC';
        $this->assertEquals($expected, $sql);
    }

    public function testApplySingleForeignOrderCondition()
    {
        $conditions = new Conditions();
        $conditions->order('author.username', 'ASC');

        $sql = $this->getSqlFromConditions($conditions);

        $expected = 'SELECT "item".* FROM "item" '
                  . 'INNER JOIN "user" AS "author" ON "item"."author_id" = "author"."u_id" '
                  . 'ORDER BY "author"."username" ASC';
        $this->assertEquals($expected, $sql);
    }

    public function testApplyMultipleForeignOrderConditions()
    {
        $conditions = new Conditions();
        $conditions->order('author.username', 'ASC');
        $conditions->order('status.name', 'DESC');

        $sql = $this->getSqlFromConditions($conditions);

        $expected = 'SELECT "item".* FROM "item" '
                  . 'INNER JOIN "user" AS "author" ON "item"."author_id" = "author"."u_id" '
                  . 'INNER JOIN "content_status" AS "status" ON "item"."status_id" = "status"."cs_id" '
                  . 'ORDER BY "author"."username" ASC, "status"."name" DESC';
        $this->assertEquals($expected, $sql);
    }

    public function testApplyPagingCondition()
    {
        $conditions = new Conditions();
        $conditions->paging(10, 10);

        $sql = $this->getSqlFromConditions($conditions);

        $expected = 'SELECT "item".* FROM "item" LIMIT \'10\' OFFSET \'10\'';
        $this->assertEquals($expected, $sql);
    }

    public function testApplyMappedColumnCondtions()
    {
        $conditions = new Conditions();
        $conditions->field('desc')->eq('testing');
        $conditions->field('longDesc')->eq('testing testing');

        $sql = $this->getSqlFromConditions($conditions);

        $expected = 'SELECT "item".* FROM "item" WHERE '
                  . 'item.description = \'testing\' AND item.long_description = \'testing testing\'';
        $this->assertEquals($expected, $sql);
    }

    public function testApplyForeignFieldMappedColumnConditions()
    {
        $conditions = new Conditions();
        $conditions->field('author.desc')->eq('testing');
        $conditions->field('status.longDesc')->eq('testing testing');

        $sql = $this->getSqlFromConditions($conditions);

        $expected = 'SELECT "item".* FROM "item" '
                  . 'INNER JOIN "user" AS "author" ON "item"."author_id" = "author"."u_id" '
                  . 'INNER JOIN "content_status" AS "status" ON "item"."status_id" = "status"."cs_id" '
                  . 'WHERE author.description = \'testing\' '
                  . 'AND status.long_description = \'testing testing\'';
        $this->assertEquals($expected, $sql);
    }

    public function testApplyDeepForeignFieldMappedColumnConditions()
    {
        $conditions = new Conditions();
        $conditions->field('author.type.desc')->eq('testing');
        $conditions->field('status.type.longDesc')->eq('testing testing');

        $sql = $this->getSqlFromConditions($conditions);

        $expected = 'SELECT "item".* FROM "item" '
                  . 'INNER JOIN "user" AS "author" ON "item"."author_id" = "author"."u_id" '
                  . 'INNER JOIN "user_type" AS "author_type" ON "author"."type_id" = "author_type"."ut_id" '
                  . 'INNER JOIN "content_status" AS "status" ON "item"."status_id" = "status"."cs_id" '
                  . 'INNER JOIN "content_status_type" AS "status_type" ON "status"."type_id" = "status_type"."cst_id" '
                  . 'WHERE author_type.description = \'testing\' '
                  . 'AND status_type.long_description = \'testing testing\'';
        $this->assertEquals($expected, $sql);
    }

    public function testApplyForeignFieldMappedColumnOrderConditions()
    {
        $conditions = new Conditions();
        $conditions->order('author.desc', 'ASC');
        $conditions->order('status.longDesc', 'DESC');

        $sql = $this->getSqlFromConditions($conditions);

        $expected = 'SELECT "item".* FROM "item" '
                  . 'INNER JOIN "user" AS "author" ON "item"."author_id" = "author"."u_id" '
                  . 'INNER JOIN "content_status" AS "status" ON "item"."status_id" = "status"."cs_id" '
                  . 'ORDER BY "author"."description" ASC, "status"."long_description" DESC';
        $this->assertEquals($expected, $sql);
    }

    public function testApplyMultipleForeignFieldConditionsOfSameType()
    {
        $conditions = new Conditions();
        $conditions->field('author.username')->eq('rwallwork');
        $conditions->field('publisher.username')->eq('rwallwork');

        $sql = $this->getSqlFromConditions($conditions);

        // @todo

        $expected = 'SELECT "item".* FROM "item" '
                  . 'INNER JOIN "user" AS "author" ON "item"."author_id" = "author"."u_id" '
                  . 'INNER JOIN "user" AS "publisher" ON "item"."publisher_id" = "publisher"."u_id" '
                  . 'WHERE author.username = \'rwallwork\' AND publisher.username = \'rwallwork\'';
        $this->assertEquals($expected, $sql);
    }

    public function testApplyMultipleDeepForeignFieldConditionsOfSameType()
    {
        $conditions = new Conditions();
        $conditions->field('author.type.active')->eq('1');
        $conditions->field('publisher.type.active')->eq('1');

        $sql = $this->getSqlFromConditions($conditions);

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
        $conditions = new Conditions();
        $conditions->field('versionOf')->eq(4);

        $sql = $this->getSqlFromConditions($conditions);

        $expected = 'SELECT "item".* FROM "item" WHERE item.version_of_id = \'4\'';
        $this->assertEquals($expected, $sql);
    }

    public function testApplyAllConditions()
    {
        $conditions = new Conditions();
        $conditions->field('field1')->eq('value1');
        $conditions->field('field2')->notEq('value2');
        $conditions->field('field3')->isNull();
        $conditions->field('field4')->isNotNull();
        $conditions->field('field5')->gt(5);
        $conditions->field('field6')->lt(6);
        $conditions->field('author.username')->eq('rwallwork');
        $conditions->field('status.name')->eq('published');
        $conditions->field('author.type.active')->eq('1');
        $conditions->field('status.type.name')->eq('global');
        $conditions->field('desc')->eq('testing');
        $conditions->field('longDesc')->eq('testing testing');
        $conditions->field('unmappedCamelCase')->eq('testing');
        $conditions->field('author.desc')->eq('testing');
        $conditions->field('status.longDesc')->eq('testing testing');
        $conditions->field('author.type.desc')->eq('testing');
        $conditions->field('status.type.longDesc')->eq('testing testing');
        $conditions->field('author')->isNull();
        $conditions->field('status.Type')->eq(8);
        $conditions->field('publisher.username')->eq('rwallwork');
        $conditions->field('publisher.type.active')->eq('1');
        $conditions->order('name', 'ASC');
        $conditions->order('date', 'DESC');
        $conditions->order('author.username', 'ASC');
        $conditions->order('status.name', 'DESC');
        $conditions->order('author.desc', 'ASC');
        $conditions->order('status.longDesc', 'DESC');
        $conditions->order('author', 'ASC');
        $conditions->order('status.type', 'DESC');
        $conditions->order('publisher.username', 'ASC');
        $conditions->paging(84, 12);

        $sql = $this->getSqlFromConditions($conditions);

        $expected = 'SELECT "item".* FROM "item" '
                  . 'INNER JOIN "user" AS "author" ON "item"."author_id" = "author"."u_id" '
                  . 'INNER JOIN "content_status" AS "status" ON "item"."status_id" = "status"."cs_id" '
                  . 'INNER JOIN "user_type" AS "author_type" ON "author"."type_id" = "author_type"."ut_id" '
                  . 'INNER JOIN "content_status_type" AS "status_type" ON "status"."type_id" = "status_type"."cst_id" '
                  . 'INNER JOIN "user" AS "publisher" ON "item"."publisher_id" = "publisher"."u_id" '
                  . 'INNER JOIN "user_type" AS "publisher_type" ON "publisher"."type_id" = "publisher_type"."ut_id" '
                  . 'WHERE item.field1 = \'value1\' AND item.field2 != \'value2\' '
                  . 'AND item.field3 IS NULL AND item.field4 IS NOT NULL AND item.field5 > \'5\' AND item.field6 < \'6\' '
                  . 'AND author.username = \'rwallwork\' AND status.name = \'published\' '
                  . 'AND author_type.active = \'1\' AND status_type.name = \'global\' '
                  . 'AND item.description = \'testing\' AND item.long_description = \'testing testing\' '
                  . 'AND item.unmapped_camel_case = \'testing\' AND author.description = \'testing\' '
                  . 'AND status.long_description = \'testing testing\' '
                  . 'AND author_type.description = \'testing\' AND status_type.long_description = \'testing testing\' '
                  . 'AND item.author_id IS NULL AND status.type_id = \'8\' '
                  . 'AND publisher.username = \'rwallwork\' AND publisher_type.active = \'1\' '
                  . 'ORDER BY "item"."name" ASC, "item"."date" DESC, "author"."username" ASC, "status"."name" DESC, '
                  . '"author"."description" ASC, "status"."long_description" DESC, "item"."author_id" ASC, '
                  . '"status"."type_id" DESC, "publisher"."username" ASC LIMIT \'12\' OFFSET \'84\'';
        $this->assertEquals($expected, $sql);
    }

}
