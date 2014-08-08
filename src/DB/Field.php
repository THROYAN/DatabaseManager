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
        if (preg_match('/^([\w\d_]+)\((.+)\)(.*)$/', $description['Type'], $matches)) {
            $type = $matches[1];
            if (in_array($type, array( 'enum', 'set' ))) {
                $params['values'] = explode(',', $matches[2]);
            } else {
                $params['length'] = $matches[2];
            }
            if ($matches[3]) {
                $params[trim($matches[3])] = true;
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
            return new $className($name, $type, $params);
        }

        return new self($name, $type, $params);
    }

    public function show($full = false)
    {
        $str = "{$this->getName()} {$this->getType()}";
        if (!$full) {
            return $str;
        }

        // тип
        // $str .= " {$this->getType()}";
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