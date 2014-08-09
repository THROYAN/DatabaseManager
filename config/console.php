<?php

return array(
    'basePath' => __DIR__ . '/..',
    'srcDir' => 'src',
    'allowedParams' => array(
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
    ),
);