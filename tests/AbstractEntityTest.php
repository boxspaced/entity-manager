<?php

require_once '_concrete/Entity.php';

class AbstractEntityTest extends PHPUnit_Framework_TestCase
{

    protected $_entity;

    public function setUp()
    {
        $this->_entity = new Entity();
    }

    public function testCanSetId()
    {
        $id = 45;

        $this->_entity->setId($id);

        $this->assertEquals($id, $this->_entity->getId());
    }

}
