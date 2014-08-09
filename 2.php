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

$config = include_once 'config/console.php';
// @TODO Сделать various
$params = Request\ConsoleRequest::parseParams($argv, $config['allowedParams'], true);

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
    foreach ($config['allowedParams'] as $param => $info) {
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
        // Используем 1 базу
        $database = new DB\Database($params['--database'], $connection);
        if (!isset($params['--table'])) {
            // выводим все таблицы
            $response = $database->show(true, $params['--showTableFields'], $params['--showRecords'], $params['--extendedFieldDescription']);
        } else {
            // работаем с 1 таблицей
            $table = new DB\Table($params['--table'], $database);
            if (isset($params['--add'])) {
                // добавляем записи
                $lines = array( );
                for ($i=0; $i < $params['--add']; $i++) {
                    $table->insert($lines[] = $table->randomRecord());
                }
                $response = \Output\TableDrawer::draw(array(
                    'head' => "Inserted records to {$params['--table']} table:",
                    'body' => array(
                        'head' => array_map(function($field) { return $field->show(false); }, $table->getStructure()),
                        'body' => $lines
                    ),
                ));
            } else {
                // выводим таблица с данными или что там кому надо
                $response = $table->show($params['--showTableFields'], $params['--showRecords'], $params['--extendedFieldDescription']);
            }
        }
    }
}

echo $response . $eol;