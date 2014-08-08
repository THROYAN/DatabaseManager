#!/usr/bin/env php
<?php

/**
 * TODO:
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

define('BASE_DIR', __DIR__ . '/src');
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
    ),
    '--database' => array(
        'description' => 'DB name',
        'various' => '-db',
        // 'required' => true,
    ),
    '--table' => array(
        'description' => 'Show more detail info about single table',
        'various' => '-t',
    ),
    '--help' => 'Show this help',
);
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
            $params[$it[0]] = $it[1];
        } else {
            $params[$it[0]] = true;
        }
    }
}

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

if (isset($params['--help'])) {
    $eol = PHP_EOL;
    $tab = "\t";
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
    echo $help;
    return;
}

$connection = new DB\Connection($params['--host'], $params['--user'], $params['--password']);

if (!isset($params['--database'])) {
    $databases = $connection->getDatabases();
    foreach ($databases as $database) {
        var_dump($database->getName(), $database->getTables());
    }

} else {
    $database = new DB\Database($params['--database'], $connection);
    foreach($database->getTables() as $table) {
        var_dump($table->getStructure());
        break;
    }
}