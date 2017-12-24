<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Export Path In Storage
    |--------------------------------------------------------------------------
    |
    | If a path is not specified in the setExportFile()
    | This folder will be the default inside storage
    | We have avoided putting anything in public as acessible via web url
    | Where as storage is safe from direct web accesss
    |
    */

    'default-folder' => 'csvexport',

    /*
    |--------------------------------------------------------------------------
    | Add timestamp to the exported file name
    |--------------------------------------------------------------------------
    |
    | If set to true, it will add time() to the file to avoid duplicates
    | We recommend to keep it true to avoid overriding
    | If this is set to false and same file exists in the folder at the time of generation
    | It will be replaced with the newly exported csv file
    |
    */

    'add-timestamp' => true,

    /*
    |--------------------------------------------------------------------------
    | Enable Execution Time Logging
    |--------------------------------------------------------------------------
    |
    | If set to true it will log the export query execution time
    | It will be helpful to compare and optimize queries in case required
    |
    */

    'enable-logging' => true,

    /*
    |--------------------------------------------------------------------------
    | Log total rows count exported when logging
    |--------------------------------------------------------------------------
    |
    | This will applicable if enable-logging is set to true
    | This configuration if set to true will add total number of rows in the logged entries
    | It will only work when setQueryBuilder() function is used and won't work on setRawQuery()
    */

    'addlog-row-count' => true

];
