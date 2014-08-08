<?php

namespace DB;

/**
* Database presentation
*/
class Database
{
    /**
     * Tables array
     * @var Table[]
     */
    private $_tables = null;

    /**
     * Connection object
     * @var Connection
     */
    private $_connection;

    /**
     * Database selected before select this
     * @var string
     */
    private $_oldDB;

    /**
     * Database name
     * @var string
     */
    private $name;

    /**
     * @param string     $name       Database name
     * @param Connection $connection Mysql connection
     */
    function __construct($name, Connection $connection)
    {
        $this->_connection = $connection;
        $this->name = $name;
    }

    /**
     * Allows to use connections method with selecting database
     * @param  string $name   method name
     * @param  array  $params method params
     * @return mixed
     */
    public function __call($name, array $params)
    {
        if (method_exists($this->_connection, $name)) {
            $this->chooseThis();
            $result = call_user_func_array(array( $this->_connection, $name ), $params);
            $this->rollbackSelectedDatabase();
            return $result;
        }
    }

    /**
     * Get tables in database
     * @return Table|Array
     */
    public function getTables()
    {
        if ($this->_tables !== null) {
            return $this->_tables;
        }
        // $this->chooseThis();

        $db = $this;
        return $this->_tables = array_map(function($table) use ($db) { return new Table($table['Tables_in_' . $db->getName()], $db); }, $this->query('SHOW TABLES', true));

        // $this->rollbackSelectedDatabase();

        // return $this->_tables;
    }

    /**
     * Selects this database for connection
     */
    public function chooseThis()
    {
        if (($oldDB = $this->_connection->getCurrentDatabase()) != $this->name) {
            $this->_oldDB = $oldDB;
            if (!$this->_connection->selectDatabase($this->name)) {
                throw new \Exception("Unknown database named {$this->name}");
            }
        }
    }

    /**
     * Backup selected database in connection
     * @param boolean $force If false it will back database only if this database is selected
     */
    public function rollbackSelectedDatabase($force = false)
    {
        if ($this->_oldDB && ($force || $this->_connection->getCurrentDatabase() == $this->name)) {
            if (!$this->_connection->selectDatabase($this->_oldDB)) {
                throw new \Exception("Unknown database named {$this->_oldDB}");
            }
        }
    }

    /**
     * Get database name
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get current connectioon
     * @return Connection
     */
    public function getConnection()
    {
        return $this->_connection;
    }

    public function __toString()
    {
        return $this->show(true, false);
    }

    public function show($showTables = true, $showTableStructure = false)
    {
        // $str = str_pad(input, pad_length)
    }
}