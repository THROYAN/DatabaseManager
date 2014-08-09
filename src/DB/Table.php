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
     * Records
     * @var array
     */
    private $_records;

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

        $table = $this;
        return $this->_structure = array_map(function($description) use ($table) { return Field::createByDescription($description, $table); }, $this->_db->query('DESCRIBE `' . $this->getName() . '`', true));
    }

    /**
     * Gets records from table
     * @param  string|null $condition SQL query (Warning: it executes raw)
     * @return array
     */
    public function getRecords($condition = null)
    {
        if (!$condition && $this->_records !== null) {
            return $this->_records;
        }

        $query = "SELECT * FROM `{$this->getName()}`";
        if ($condition) {
            return $this->_db->query("{$query} {$condition}", true);
        }
        return $this->_records = $this->_db->query($query, true);
    }

    /**
     * Get name
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    public function getDatabase()
    {
        return $this->_db;
    }

    public function __toString()
    {
        return $this->show();
    }

    /**
     * Shows table
     * @param  boolean $showStructure         Display fields
     * @param  boolean|int $showRecords           Display records, can be number of last records
     * @param  boolean $extendedStructureInfo Display full information of each column (Doesn't work if $showRecords isn't false)
     * @return string
     */
    public function show($showStructure = false, $showRecords = false, $extendedStructureInfo = false)
    {
        $caption = $this->getName() . " (" . count($this->getRecords()) . " records)";
        if (!$showStructure && !$showRecords) {
            return $caption;
        }

        if ($showRecords) {
            // расчитываем нужное количество
            $totalCount = count($this->getRecords());
            if (is_numeric($showRecords) && $showRecords < $totalCount) {
                $condition = "LIMIT " . ($totalCount - $showRecords) . ", {$showRecords}";
            } else {
                $condition = null;
            }

            // с шапкой (полями) или без
            if ($showStructure) {
                $fields = array(
                    'head' => array_map(function($field) { return $field->show(false); }, $this->getStructure()),
                    'body' => $this->getRecords($condition),
                );
            } else {
                $fields = $this->getRecords($condition);
            }
        } else {
            $fields = array_map(function($field) use ($extendedStructureInfo) { return $field->show($extendedStructureInfo); }, $this->getStructure());
        }

        $table = array(
            'head' => $caption,
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

    public function insert($record)
    {
        if (!$record) {
            return false;
        }
        $keys = implode(',', array_map(function($field) { return "`" . $this->_db->escapeString($field) . "`"; }, array_keys($record)));
        $values = implode(",", array_map(function($value) { return "'" . $this->_db->escapeString($value) . "'"; }, $record));
        $sql = "INSERT INTO `{$this->getName()}` ({$keys}) VALUES({$values})";
        return $this->_db->query($sql);
    }

    /**
     * Creates random record depends on structure
     * @return array
     */
    public function randomRecord()
    {
        $record = array();
        foreach ($this->getStructure() as $field) {
            if (($value = $field->randomValue()) !== null) {
                $record[$field->getName()] = $value;
            }
        }
        return $record;
    }
}