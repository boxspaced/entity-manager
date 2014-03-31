<?php

class Entity implements EntityManager_EntityInterface
{

    protected $_id;
    protected $_title;
    protected $_fname;
    protected $_lname;

    public function getId()
    {
        return $this->_id;
    }

    public function setId($id)
    {
        $this->_id = $id;
        return $this;
    }

    public function getTitle()
    {
        return $this->_title;
    }

    public function setTitle($title)
    {
        $this->_title = $title;
        return $this;
    }

    public function getFname()
    {
        return $this->_fname;
    }

    public function setFname($fname)
    {
        $this->_fname = $fname;
        return $this;
    }

    public function getLname()
    {
        return $this->_lname;
    }

    public function setLname($lname)
    {
        $this->_lname = $lname;
        return $this;
    }

}
