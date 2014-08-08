<?php

namespace DB;

/**
* Table field
*/
class Field
{
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

    function __construct($name, $type, array $params = array())
    {
        $this->name = $name;
        $this->type = $type;
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
        // switch ($this->type) {
        //     case 'value':
        //         # code...
        //         break;

        //     default:
        //         # code...
        //         break;
        // }
    }

    public static function createByDescription(array $description)
    {
        if (array_diff(array('Field', 'Type', 'Extra', 'Null', 'Key', 'Default'), array_keys($description))) {
            throw new Exception("Invalid field description");
        }
        $name = $description['Field'];
        $params = array();
        // Разбираем тип
        if (preg_match('/^([\w\d_]+)\((.+)\)$/', $description['Type'], $matches)) {
            $type = $matches[1];
            if (in_array($type, array( 'enum', 'set' ))) {
                $params['values'] = $matches[2];
            } else {
                $params['length'] = $matches[2];
            }
        } else {
            $type = $description['Type'];
        }

        $params['default'] = $description['Default'];
        $params['nullable'] = $description['Null'] == 'Yes';
        $params['autoIncrement'] = $description['Extra'] == 'auto_increment';
        $params['key'] = $description['Key'];
        return new self($name, $type, $params);
    }

}