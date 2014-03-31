<?php

class MapperConditionsTest extends PHPUnit_Framework_TestCase
{

    protected $_conditions;

    public function setUp()
    {
        $this->_conditions = new EntityManager_Mapper_Conditions_Conditions();
    }

    public function testCannotStartNewFieldUntilLastIsCompleted()
    {
        $this->setExpectedException('EntityManager_Mapper_Conditions_Exception');

        $this->_conditions->field('test')->field('test2');
    }

    public function testCannotAddOperatorAndValueUnlessLastFieldIsIncomplete()
    {
        $this->setExpectedException('EntityManager_Mapper_Conditions_Exception');

        $this->_conditions->field('test')->eq('value')->eq('value');
    }

    public function testAddValidEqualsField()
    {
        $field = 'field';
        $value = 'value';

        $this->_conditions->field($field)->eq($value);
        $fields = $this->_conditions->getFields();

        $this->assertEquals($field, $fields[0]->getName());
        $this->assertEquals(
            EntityManager_Mapper_Conditions_Conditions::OPERATOR_EQUALS,
            $fields[0]->getOperator()
        );
        $this->assertEquals($value, $fields[0]->getValue());
    }

    public function testAddValidNotEqualsField()
    {
        $field = 'field';
        $value = 'value';

        $this->_conditions->field($field)->notEq($value);
        $fields = $this->_conditions->getFields();

        $this->assertEquals($field, $fields[0]->getName());
        $this->assertEquals(
            EntityManager_Mapper_Conditions_Conditions::OPERATOR_NOT_EQUALS,
            $fields[0]->getOperator()
        );
        $this->assertEquals($value, $fields[0]->getValue());
    }

    public function testAddValidIsNullField()
    {
        $field = 'field';
        $value = 'value';

        $this->_conditions->field($field)->isNull();
        $fields = $this->_conditions->getFields();

        $this->assertEquals($field, $fields[0]->getName());
        $this->assertEquals(
            EntityManager_Mapper_Conditions_Conditions::OPERATOR_IS,
            $fields[0]->getOperator()
        );
        $this->assertInstanceOf('EntityManager_Mapper_Conditions_Expr', $fields[0]->getValue());
        $this->assertEquals('NULL', (string) $fields[0]->getValue());
    }

    public function testAddValidIsNotNullField()
    {
        $field = 'field';
        $value = 'value';

        $this->_conditions->field($field)->isNotNull();
        $fields = $this->_conditions->getFields();

        $this->assertEquals($field, $fields[0]->getName());
        $this->assertEquals(
            EntityManager_Mapper_Conditions_Conditions::OPERATOR_IS_NOT,
            $fields[0]->getOperator()
        );
        $this->assertInstanceOf('EntityManager_Mapper_Conditions_Expr', $fields[0]->getValue());
        $this->assertEquals('NULL', (string) $fields[0]->getValue());
    }

    public function testAddValidGreaterThanField()
    {
        $field = 'field';
        $value = 'value';

        $this->_conditions->field($field)->gt($value);
        $fields = $this->_conditions->getFields();

        $this->assertEquals($field, $fields[0]->getName());
        $this->assertEquals(
            EntityManager_Mapper_Conditions_Conditions::OPERATOR_GREATER_THAN,
            $fields[0]->getOperator()
        );
        $this->assertEquals($value, $fields[0]->getValue());
    }

    public function testAddValidLessThanField()
    {
        $field = 'field';
        $value = 'value';

        $this->_conditions->field($field)->lt($value);
        $fields = $this->_conditions->getFields();

        $this->assertEquals($field, $fields[0]->getName());
        $this->assertEquals(
            EntityManager_Mapper_Conditions_Conditions::OPERATOR_LESS_THAN,
            $fields[0]->getOperator()
        );
        $this->assertEquals($value, $fields[0]->getValue());
    }

    public function testInvalidOrderDirectionThrowsException()
    {
        $this->setExpectedException('EntityManager_Mapper_Conditions_Exception');

        $this->_conditions->order('test', 'BAD_DIR');
    }

    public function testAddValidOrderClause()
    {
        $field1 = 'field1';
        $dir1 = EntityManager_Mapper_Conditions_Conditions::ORDER_ASC;
        $field2 = 'field2';
        $dir2 = EntityManager_Mapper_Conditions_Conditions::ORDER_DESC;

        $this->_conditions->order($field1, $dir1);
        $this->_conditions->order($field2, $dir2);

        $order = $this->_conditions->getOrder();
        $this->assertEquals($field1, $order[0]->getField()->getName());
        $this->assertEquals($dir1, $order[0]->getDirection());
        $this->assertEquals($field2, $order[1]->getField()->getName());
        $this->assertEquals($dir2, $order[1]->getDirection());
    }

    public function testInvalidPagingArgsDefaultToValidValues()
    {
        $this->_conditions->paging('gg', 'jj');

        $paging = $this->_conditions->getPaging();
        $this->assertEquals(0, $paging->getOffset());
        $this->assertEquals(10, $paging->getShowPerPage());
    }

    public function testAddValidPagingClause()
    {
        $page = 2;
        $rowCount = 10;

        $this->_conditions->paging($page, $rowCount);

        $paging = $this->_conditions->getPaging();
        $this->assertEquals($page, $paging->getOffset());
        $this->assertEquals($rowCount, $paging->getShowPerPage());
    }

}
