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

    public function __toString()
    {
        return $this->show();
    }

    public function show($showStructure = false, $extendedStructureInfo = false)
    {
        if (!$showStructure) {
            return $this->getName();
        }

        $fields = array_map(function($field) use ($extendedStructureInfo) { return $field->show($extendedStructureInfo); }, $this->getStructure());

        $table = array(
            'head' => $this->getName(),
            'body' => $fields,
        );
        return \Output\TableDrawer::draw($table);

        $lines = array( $this->getName() );


        $maxLength = strlen( $this->getName() );
        foreach ($fields as $fieldStr) {
            foreach (explode("\n", $fieldStr) as $str) {
                $lines[] = $str;
                if (strlen($str) > $maxLength) {
                    $maxLength = strlen($str);
                }
            }
        }
        $maxLength += 2;
        $underscore = implode("", array_fill(0, $maxLength, '-'));
        array_splice($lines, 1, 0, $underscore); // after name
        array_splice($lines, 0, 0, $underscore); // before name
        $lines[] = $underscore; // last line

        return implode("\n", array_map(function($str) use ($maxLength) { return "|" . str_pad($str, $maxLength, ' ', STR_PAD_BOTH) . "|"; }, $lines));
    }
}