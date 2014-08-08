<?php

namespace DB;

/**
* Table presentation
*/
class Table
{
    /**
     * Database
     * @var Database
     */
    private $_db;

    /**
     * Table name
     * @var string
     */
    private $name;

    /**
     * Structure of the table
     * @var array
     */
    private $_structure;

    /**
     * @param string     $name       Table name
     * @param Database $db mysql database
     */
    function __construct($name, Database $db)
    {
        $this->_db = $db;
        $this->name = $name;
    }

    /**
     * Get table structure
     * @return array
     */
    public function getStructure()
    {
        if ($this->_structure !== null) {
            return $this->_structure;
        }

        return $this->_structure = array_map(function($description) { return Field::createByDescription($description); }, $this->_db->query('DESCRIBE `' . $this->getName() . '`', true));
    }

    /**
     * Get name
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}