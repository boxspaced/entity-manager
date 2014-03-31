<?php

require_once '_concrete/Entity.php';

class AbstractEntityTest extends PHPUnit_Framework_TestCase
{

    protected $entity;

    public function setUp()
    {
        $this->entity = new Entity();
    }

    public function testCanSetId()
    {
        $id = 45;
        
        $this->entity->setId($id);

        $this->assertEquals($id, $this->entity->getId());
    }

}
