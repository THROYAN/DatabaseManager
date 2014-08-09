<?php

namespace Request;

/**
* Console request
*/
class ConsoleRequest
{
    public function parseParams($parameters, $allowedParams = array(), $onlyAllowed = true)
    {
        // parse params
        $params = array( );
        if ($parameters) {
            foreach ($parameters as $k => $v)
            {
                if ($k == 0) {
                    continue;
                }
                $it = explode("=", $parameters[$k]);
                if ($onlyAllowed && !array_key_exists($it[0], $allowedParams)) {
                    throw new \Exception("Unknown argument \"{$it[0]}\". Try one of this " . implode(', ', array_keys($allowedParams)));
                }

                if (isset($it[1])) {
                    $params[$it[0]] = $it[1] === 'false' ? false : $it[1];
                } else {
                    $params[$it[0]] = true;
                }
            }
        }

        // check required params
        foreach ($allowedParams as $param => $info) {
            if (is_numeric($param) && is_string($info)) {
                $param = $info;
            }

            if (!is_array($info)) {
                $info = array( 'description' => $info, );
            }
            if (array_key_exists('required', $info) && $info['required'] && !array_key_exists($param, $params)) {
                throw new \Exception("Parameter \"{$param}\" is required");
            }
            if (array_key_exists('default', $info) && !array_key_exists($param, $params)) {
                $params[$param] = $info['default'];
            }
        }

        // check dependecies between params
        foreach ($params as $param => $value) {
            $info = $allowedParams[$param];
            if (array_key_exists('requires', $info)) {
                foreach ((array)$info['requires'] as $neededParam => $neededValue) {
                    if (is_numeric($neededParam)) {
                        $neededParam = $neededValue;
                        $neededValue = true;
                    }
                    if (
                        (array_key_exists($neededParam, $params) != $neededValue)
                        || (array_key_exists($neededParam, $params) && $params[$neededParam] != $neededValue)
                    ) {
                        throw new \Exception("Parameter \"{$param}\" requires \"{$neededParam}\" parameter to be setted");
                    }
                }
            }
        }
        return $params;
    }
}