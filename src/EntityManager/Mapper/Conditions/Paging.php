<?php

class EntityManager_Mapper_Conditions_Paging
{

    /**
     * @var int
     */
    protected $_offset;

    /**
     * @var int
     */
    protected $_showPerPage;

    /**
     * @param int $offset
     * @param int $showPerPage
     */
    public function __construct($offset, $showPerPage)
    {
        $this->_offset = (int) $offset;
        $this->_showPerPage = (int) $showPerPage;
    }

    /**
     * @return int
     */
    public function getOffset()
    {
        return $this->_offset;
    }

    /**
     * @return int
     */
    public function getShowPerPage()
    {
        return $this->_showPerPage;
    }

}
