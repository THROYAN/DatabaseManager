#!/usr/bin/env php
<?php

/**
 * @TODO:
 *
 * DB Data Generator
 *
 * Необходимо написать программу для генерации тестовых данных. БД — MySQL.
 *
 * 1) Пользователь указывает программе необходимые для установки соединения с БД данные доступа.
 *     Заранее её структура неизвестна. Программа должна "узнать" структуру
 *     (таблицы, поля, типы полей, ключи, количество записей в таблицах, и проч.), и отобразить в удобочитаемом виде.
 *     Сами записи можно не отображать.
 *
 * 2) Для каждой таблицы должна быть возможность заполнения автоматически-генерируемыми данными в соответствии со структурой,
 *     с указанием количества строк которые нужно добавить.
 *     При заполнении необходимо учитывать требования ключей, автоинкрементных полей и т.д.
 *
 * 3) После генерации должна быть возможность просмотреть данные, которые были сгенерированы.
 *
 */

ini_set('max_execution_time', 0);

defined('BASE_DIR') or define('BASE_DIR', __DIR__ . '/src');
include_once 'Core/bootstrap.php';

// $env = new Core\Environment( 'console' );
// $env->makeRainbow()->show();

// $app = new App();
// $request = $app->generateRequest();
// $response = $app->proceedRequest($request);
// $response->show();
// $app->end();

$allowedParams = array(
    '--host' => array(
        'description' => 'Host to connect',
        'various' => '-h',
        'default' => 'localhost',
    ),
    '--user' => array(
        'description' => 'User name for connection to DB',
        'various' => '-u',
        'default' => 'root',
    ),
    '--password' => array(
        'description' => 'Password for connection to DB',
        'various' => '-p',
        'default' => '',
    ),
    '--database' => array(
        'description' => 'DB name',
        'various' => '-db',
        // 'required' => true,
    ),
    '--table' => array(
        'description' => 'Show more detail info about single table',
        'various' => '-t',
        'requires' => '--database',
    ),
    '--showTables' => array(
        'description' => 'Show tables from each database (if --database isn\'t setted)',
        'default' => false,
    ),
    '--showTableFields' => array(
        'description' => 'Show table structure (only if --showTables or --database is setted)',
        'default' => false,
    ),
    '--showRecords' => array(
        'description' => 'Show table\'s content',
        'default' => false,
    ),
    '--extendedFieldDescription' => array(
        'description' => 'Show additional info about each field in table (only if --showTableFields is setted)',
        'default' => false,
    ),
    '--non-utf8' => array(
        'description' => 'Show ouput in non-utf8 mode',
        'default' => false,
    ),
    '--add' => array(
        'description' => 'Count of records to insert in table',
        'requires' => '--table',
    ),
    // '--html' => array(
    //     'description' => 'Show output as html',
    //     'default' => false,
    // ),
    '--help' => 'Show this help',
);
// @TODO Сделать various
// parse params
$params = array( );
if ($argv) {
    foreach ($argv as $k => $v)
    {
        if ($k == 0) {
            continue;
        }
        $it = explode("=", $argv[$k]);
        if (!array_key_exists($it[0], $allowedParams)) {
            throw new Exception("Unknown argument \"{$it[0]}\". Try one of this " . implode(', ', array_keys($allowedParams)));
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
        throw new Exception("Parameter \"{$param}\" is required");
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
                throw new Exception("Parameter \"{$param}\" requires \"{$neededParam}\" parameter to be setted");
            }
        }
    }
}

if (!$params['--non-utf8']) {
    Output\TableDrawer::$symbols = array(
        'underscore' => '─',
        'underscoreLeftBorder' => '╟',
        'underscoreRightBorder' => '╢',
        'topBorder' => '═',
        'topLeftBorder' => '╔',
        'topRightBorder' => '╗',
        'bottomBorder' => '═',
        'bottomLeftBorder' => '╚',
        'bottomRightBorder' => '╝',
        'leftBorder' => '║',
        'rightBorder' => '║',
        'newLine' => ( isset($params['--html']) && $params['--html'] ) ? '<br/>' : PHP_EOL,
        'columnSeparator' => '│',
        // 'pad' => ( isset($params['--html']) && $params['--html'] ) ? '&nbsp;' : ' ',
    );
}

$eol = PHP_EOL;
$tab = "    ";
if (isset($params['--help'])) {
    $help = $eol;
    foreach ($allowedParams as $param => $info) {
        if (!is_array($info)) {
            $info = array( 'description' => $info );
        }
        if (!isset($info['description'])) {
            $info['description'] = "NO DESCRIPTION";
        }

        $help .= "{$param}";
        if (isset($info['various'])) {
            $help .= "(" . implode(', ', (array)$info['various']) . ")";
        }
        $help .= ":{$eol}{$tab}" . implode("{$eol}{$tab}", explode($eol, $info['description']));

        $help .= $eol;
    }
    $response = $help;
} else {

    $connection = new DB\Connection($params['--host'], $params['--user'], $params['--password']);

    // выводим все базы
    if (!isset($params['--database'])) {
        $databases = $connection->getDatabases();
        $response = "List of databases:{$eol}";
        foreach ($databases as $database) {
            $response .= "{$database->show($params['--showTables'], $params['--showTableFields'], $params['--showRecords'], $params['--extendedFieldDescription'])}{$eol}";
        }
    } else {
        $database = new DB\Database($params['--database'], $connection);
        if (!isset($params['--table'])) {
            $response = $database->show(true, $params['--showTableFields'], $params['--showRecords'], $params['--extendedFieldDescription']);
        } else {
            $table = new DB\Table($params['--table'], $database);
            if (isset($params['--add'])) {
                $lines = array( );
                for ($i=0; $i < $params['--add']; $i++) {
                    $table->insert($lines[] = $table->randomRecord());
                }
                var_dump($lines);
                $response = \Output\TableDrawer::draw(array(
                    'head' => "Inserted records to {$params['--table']} table:",
                    'body' => array(
                        'head' => array_map(function($field) { return $field->show(false); }, $table->getStructure()),
                        'body' => $lines
                    ),
                ));
            } else {
                $response = $table->show($params['--showTableFields'], $params['--showRecords'], $params['--extendedFieldDescription']);
            }
        }
    }
}

echo $response . $eol;