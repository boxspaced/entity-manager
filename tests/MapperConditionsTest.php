<?php
namespace Boxspaced\EntityManager\Test;

use Boxspaced\EntityManager\Mapper\Query;
use Boxspaced\EntityManager\Mapper\Expr;
use Boxspaced\EntityManager\Exception;

class MapperQueryTest extends \PHPUnit_Framework_TestCase
{

    protected $query;

    public function setUp()
    {
        $this->query = new Query();
    }

    public function testCannotStartNewFieldUntilLastIsCompleted()
    {
        $this->setExpectedException(Exception\UnexpectedValueException::class);

        $this->query->field('test')->field('test2');
    }

    public function testCannotAddOperatorAndValueUnlessLastFieldIsIncomplete()
    {
        $this->setExpectedException(Exception\UnexpectedValueException::class);

        $this->query->field('test')->eq('value')->eq('value');
    }

    public function testAddValidEqualsField()
    {
        $field = 'field';
        $value = 'value';

        $this->query->field($field)->eq($value);
        $fields = $this->query->getFields();

        $this->assertEquals($field, $fields[0]->getName());
        $this->assertEquals(
            Query::OPERATOR_EQUALS,
            $fields[0]->getOperator()
        );
        $this->assertEquals($value, $fields[0]->getValue());
    }

    public function testAddValidNotEqualsField()
    {
        $field = 'field';
        $value = 'value';

        $this->query->field($field)->notEq($value);
        $fields = $this->query->getFields();

        $this->assertEquals($field, $fields[0]->getName());
        $this->assertEquals(
            Query::OPERATOR_NOT_EQUALS,
            $fields[0]->getOperator()
        );
        $this->assertEquals($value, $fields[0]->getValue());
    }

    public function testAddValidIsNullField()
    {
        $field = 'field';
        $value = 'value';

        $this->query->field($field)->isNull();
        $fields = $this->query->getFields();

        $this->assertEquals($field, $fields[0]->getName());
        $this->assertEquals(
            Query::OPERATOR_IS,
            $fields[0]->getOperator()
        );
        $this->assertInstanceOf(Expr::class, $fields[0]->getValue());
        $this->assertEquals('NULL', (string) $fields[0]->getValue());
    }

    public function testAddValidIsNotNullField()
    {
        $field = 'field';
        $value = 'value';

        $this->query->field($field)->isNotNull();
        $fields = $this->query->getFields();

        $this->assertEquals($field, $fields[0]->getName());
        $this->assertEquals(
            Query::OPERATOR_IS_NOT,
            $fields[0]->getOperator()
        );
        $this->assertInstanceOf(Expr::class, $fields[0]->getValue());
        $this->assertEquals('NULL', (string) $fields[0]->getValue());
    }

    public function testAddValidGreaterThanField()
    {
        $field = 'field';
        $value = 'value';

        $this->query->field($field)->gt($value);
        $fields = $this->query->getFields();

        $this->assertEquals($field, $fields[0]->getName());
        $this->assertEquals(
            Query::OPERATOR_GREATER_THAN,
            $fields[0]->getOperator()
        );
        $this->assertEquals($value, $fields[0]->getValue());
    }

    public function testAddValidLessThanField()
    {
        $field = 'field';
        $value = 'value';

        $this->query->field($field)->lt($value);
        $fields = $this->query->getFields();

        $this->assertEquals($field, $fields[0]->getName());
        $this->assertEquals(
            Query::OPERATOR_LESS_THAN,
            $fields[0]->getOperator()
        );
        $this->assertEquals($value, $fields[0]->getValue());
    }

    public function testInvalidOrderDirectionThrowsException()
    {
        $this->setExpectedException(Exception\InvalidArgumentException::class);

        $this->query->order('test', 'BAD_DIR');
    }

    public function testAddValidOrderClause()
    {
        $field1 = 'field1';
        $dir1 = Query::ORDER_ASC;
        $field2 = 'field2';
        $dir2 = Query::ORDER_DESC;

        $this->query->order($field1, $dir1);
        $this->query->order($field2, $dir2);

        $order = $this->query->getOrder();
        $this->assertEquals($field1, $order[0]->getField()->getName());
        $this->assertEquals($dir1, $order[0]->getDirection());
        $this->assertEquals($field2, $order[1]->getField()->getName());
        $this->assertEquals($dir2, $order[1]->getDirection());
    }

    public function testInvalidPagingArgsDefaultToValidValues()
    {
        $this->query->paging('gg', 'jj');

        $paging = $this->query->getPaging();
        $this->assertEquals(0, $paging->getOffset());
        $this->assertEquals(10, $paging->getShowPerPage());
    }

    public function testAddValidPagingClause()
    {
        $page = 2;
        $rowCount = 10;

        $this->query->paging($page, $rowCount);

        $paging = $this->query->getPaging();
        $this->assertEquals($page, $paging->getOffset());
        $this->assertEquals($rowCount, $paging->getShowPerPage());
    }

}
