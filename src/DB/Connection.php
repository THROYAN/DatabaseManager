<?php

namespace DB;

/**
* Connection to server with databases
*/
class Connection
{
    /**
     * mysql connection
     * @var mysqli
     */
    private $resource;

    function __construct($host, $username = 'root', $password = '')
    {
        $this->resource = mysqli_connect($host, $username, $password);
        if ($this->resource->connect_errno) {
            throw new \Exception("Cannot connect: {$this->resource->connect_error}");
        }
    }

    /**
     * Executes query and returns result
     * @param  string  $command query
     * @param  boolean $assoc   return each result as assoc arrays
     * @return array
     */
    public function query($command, $assoc = false)
    {
        $q = $this->resource->query($command);
        if ($q === true) {
            return true;
        }
        if ($q === false) {
            throw new \Exception("Cannot execute query \"{$command}\":\n" . $this->resource->error);
        }

        $result = array();
        while ( $q && ($row = $assoc ? $q->fetch_assoc() : $q->fetch_object()) ) {
            $result[] = $row;
        }
        if ($q) {
            $q->close();
        }
        return $result;
    }

    /**
     * Get all databases
     * @return Database[]
     */
    public function getDatabases()
    {
        $con = $this;
        return array_map( function($d) use ($con) { return new Database($d['Database'], $con); }, $this->query('SHOW DATABASES', true) );
    }

    /**
     * Change selected database
     * @param  string $dbName
     * @return boolean Successness of operation
     */
    public function selectDatabase($dbName)
    {
        return $this->resource->select_db($dbName);
    }

    /**
     * Returns current selected database.
     * Return false if nothing is select.
     * @return string|boolean
     */
    public function getCurrentDatabase()
    {
        $r = $this->query('SELECT DATABASE() as `dbName`', true);
        return isset($r[0]['dbName']) ? $r[0]['dbName'] : false;
    }
}