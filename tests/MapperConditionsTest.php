<?php
namespace Boxspaced\EntityManager\Test;

use Boxspaced\EntityManager\Mapper\Conditions\Conditions;

class MapperConditionsTest extends \PHPUnit_Framework_TestCase
{

    protected $conditions;

    public function setUp()
    {
        $this->conditions = new Conditions();
    }

    public function testCannotStartNewFieldUntilLastIsCompleted()
    {
        $this->setExpectedException('UnexpectedValueException');

        $this->conditions->field('test')->field('test2');
    }

    public function testCannotAddOperatorAndValueUnlessLastFieldIsIncomplete()
    {
        $this->setExpectedException('UnexpectedValueException');

        $this->conditions->field('test')->eq('value')->eq('value');
    }

    public function testAddValidEqualsField()
    {
        $field = 'field';
        $value = 'value';

        $this->conditions->field($field)->eq($value);
        $fields = $this->conditions->getFields();

        $this->assertEquals($field, $fields[0]->getName());
        $this->assertEquals(
            Conditions::OPERATOR_EQUALS,
            $fields[0]->getOperator()
        );
        $this->assertEquals($value, $fields[0]->getValue());
    }

    public function testAddValidNotEqualsField()
    {
        $field = 'field';
        $value = 'value';

        $this->conditions->field($field)->notEq($value);
        $fields = $this->conditions->getFields();

        $this->assertEquals($field, $fields[0]->getName());
        $this->assertEquals(
            Conditions::OPERATOR_NOT_EQUALS,
            $fields[0]->getOperator()
        );
        $this->assertEquals($value, $fields[0]->getValue());
    }

    public function testAddValidIsNullField()
    {
        $field = 'field';
        $value = 'value';

        $this->conditions->field($field)->isNull();
        $fields = $this->conditions->getFields();

        $this->assertEquals($field, $fields[0]->getName());
        $this->assertEquals(
            Conditions::OPERATOR_IS,
            $fields[0]->getOperator()
        );
        $this->assertInstanceOf('Boxspaced\\EntityManager\\Mapper\\Conditions\\Expr', $fields[0]->getValue());
        $this->assertEquals('NULL', (string) $fields[0]->getValue());
    }

    public function testAddValidIsNotNullField()
    {
        $field = 'field';
        $value = 'value';

        $this->conditions->field($field)->isNotNull();
        $fields = $this->conditions->getFields();

        $this->assertEquals($field, $fields[0]->getName());
        $this->assertEquals(
            Conditions::OPERATOR_IS_NOT,
            $fields[0]->getOperator()
        );
        $this->assertInstanceOf('Boxspaced\\EntityManager\\Mapper\\Conditions\\Expr', $fields[0]->getValue());
        $this->assertEquals('NULL', (string) $fields[0]->getValue());
    }

    public function testAddValidGreaterThanField()
    {
        $field = 'field';
        $value = 'value';

        $this->conditions->field($field)->gt($value);
        $fields = $this->conditions->getFields();

        $this->assertEquals($field, $fields[0]->getName());
        $this->assertEquals(
            Conditions::OPERATOR_GREATER_THAN,
            $fields[0]->getOperator()
        );
        $this->assertEquals($value, $fields[0]->getValue());
    }

    public function testAddValidLessThanField()
    {
        $field = 'field';
        $value = 'value';

        $this->conditions->field($field)->lt($value);
        $fields = $this->conditions->getFields();

        $this->assertEquals($field, $fields[0]->getName());
        $this->assertEquals(
            Conditions::OPERATOR_LESS_THAN,
            $fields[0]->getOperator()
        );
        $this->assertEquals($value, $fields[0]->getValue());
    }

    public function testInvalidOrderDirectionThrowsException()
    {
        $this->setExpectedException('InvalidArgumentException');

        $this->conditions->order('test', 'BAD_DIR');
    }

    public function testAddValidOrderClause()
    {
        $field1 = 'field1';
        $dir1 = Conditions::ORDER_ASC;
        $field2 = 'field2';
        $dir2 = Conditions::ORDER_DESC;

        $this->conditions->order($field1, $dir1);
        $this->conditions->order($field2, $dir2);

        $order = $this->conditions->getOrder();
        $this->assertEquals($field1, $order[0]->getField()->getName());
        $this->assertEquals($dir1, $order[0]->getDirection());
        $this->assertEquals($field2, $order[1]->getField()->getName());
        $this->assertEquals($dir2, $order[1]->getDirection());
    }

    public function testInvalidPagingArgsDefaultToValidValues()
    {
        $this->conditions->paging('gg', 'jj');

        $paging = $this->conditions->getPaging();
        $this->assertEquals(0, $paging->getOffset());
        $this->assertEquals(10, $paging->getShowPerPage());
    }

    public function testAddValidPagingClause()
    {
        $page = 2;
        $rowCount = 10;

        $this->conditions->paging($page, $rowCount);

        $paging = $this->conditions->getPaging();
        $this->assertEquals($page, $paging->getOffset());
        $this->assertEquals($rowCount, $paging->getShowPerPage());
    }

}
