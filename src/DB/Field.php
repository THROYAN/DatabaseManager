<?php

namespace DB;

/**
* Table field
*/
class Field
{
    public static $keys = array(
        'PRI' => 'Primary',
        'UNI' => 'Unique',
        'MUL' => 'Multiple',
    );

    /**
     * Table-owner
     * @var Table
     */
    private $table;

    /**
     * Field name
     * @var string
     */
    private $name;

    /**
     * Field type
     * @var string
     */
    private $type;

    /**
     * Field additional params
     * @var array
     */
    private $params;

    function __construct($name, $type, Table $table, array $params = array())
    {
        $this->name = $name;
        $this->type = $type;
        $this->table = $table;
        $this->params = $params;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getParams()
    {
        return $this->params;
    }

    public function randomValue()
    {
        // автоинкремент
        if (isset($this->params['autoIncrement']) && $this->params['autoIncrement']) {
            return null;
        }

        $value = null;
        switch ($this->type) {
            case 'tinyint': case 'smallint': case 'mediumint': case'int': case 'bigint':
                $intSizes = array(
                    'tinyint' => 1,
                    'smallint' => 2,
                    'mediumint' => 3,
                    'int' => 4,
                    'bigint' => 8,
                );
                $length = pow(2, 8 * (/*isset($this->params['length']) ? $this->params['length'] : */$intSizes[$this->type]));
                $value = rand(
                    isset($this->params['unsigned']) ? 0 : (-1 * $length / 2), // min value
                    (isset($this->params['unsigned']) ? $length : ($length / 2)) - 1 // max value
                );
                break;
            case 'varchar': case 'char': case 'text':
                $length = rand(0, isset($this->params['length']) ? $this->params['length'] : 255);
                $value = \Output\CommonFunctions::generateRandomString($length);
                break;
        }
        // уникальный
        if (isset($this->params['key']) && in_array( $this->params['key'], array('Unique', 'Primary') )) {
            if (in_array($value, $this->getAllValues())) {
                $value = $this->randomValue();
            }
        }
        return $value;
    }

    public function getAllValues()
    {
        $self = $this;
        return array_map(function($record) use ($self) { return $record[$self->getName()]; }, $this->table->getDatabase()->query( "SELECT `{$this->getName()}` FROM `{$this->table->getName()}", true ));
    }

    public static function createByDescription(array $description, Table $table)
    {
        if (array_diff(array('Field', 'Type', 'Extra', 'Null', 'Key', 'Default'), array_keys($description))) {
            throw new Exception("Invalid field description");
        }
        $name = $description['Field'];
        $params = array();
        // Разбираем тип
        if (preg_match('/^([\w\d_]+)\((.+)\)(.*)$/', $description['Type'], $matches)) {
            $type = $matches[1];
            if (in_array($type, array( 'enum', 'set' ))) {
                $params['values'] = explode(',', $matches[2]);
            } else {
                $params['length'] = $matches[2];
            }
            if ($matches[3]) {
                foreach (explode(' ', $matches[3]) as $value) {
                    $params[trim($value)] = true;
                }
            }
        } else {
            $type = $description['Type'];
        }

        $params['nullable'] = $description['Null'] == 'YES';
        $params['default'] = $description['Default'];
        $params['autoIncrement'] = $description['Extra'] == 'auto_increment';
        if (array_key_exists($description['Key'], self::$keys)) {
            $params['key'] = self::$keys[$description['Key']];
        }

        $className = explode("\\", __CLASS__);
        $className[] = ucfirst($type) . array_pop($className);
        $className = implode("\\", $className);

        if (class_exists($className)) {
            return new $className($name, $type, $table, $params);
        }

        return new self($name, $type, $table, $params);
    }

    public function show($full = false)
    {
        $str = "{$this->getName()}";// ({$this->getType()})";
        if (!$full) {
            return $str;
        }

        // тип
        $str .= " {$this->getType()}";
        if (isset($this->params['values'])) {
            $str .= "(" . implode(', ', $this->params['values']) . ")";
        } elseif (isset($this->params['length'])) {
            $str .= "({$this->params['length']})";
        }
        // unsigned
        if (isset($this->params['unsigned'])) {
            $str .= " unsigned";
        }
        // автоинкремент
        if (isset($this->params['autoIncrement'])) {
            $str .= " AUTO_INCREMENT";
        }
        // NULL
        if (isset($this->params['nullable']) && $this->params['nullable']) {
            $str .= " NULLABLE";
        } else {
            $str .= " NOT_NULLABLE";
        }
        // DEFAULT
        if (array_key_exists('default', $this->params)) {
            $str .= " (DEFAULT: " . ($this->params['default'] ? "'{$this->params['default']}'" : "NULL") . ")";
        }
        // ключ
        if (isset($this->params['key']) && $this->params['key']) {
            $str .= " {$this->params['key']}Key";
        }
        return $str;
    }

}