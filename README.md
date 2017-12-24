# Laravel Export To CSV natively in MySQL

Supports Laravel 5.2 and above

Many applications require an export to csv functionality. It's implemented in variety of ways, however many of us face a performance bottleneck when the data to be exported is very huge. PHP application may consum lot of memory resources for each export. 

This package is a solution for it where it uses MySQL's SELECT INTO OUTFILE approach. The entire export is performed by MySQL natively. It's extrmely efficient, quick. It eliminates overhead from PHP application utilizing memory to pass selected the query collection for generating CSV. This is extremely handy for systems having large set of rows (like ~1,00,000 row count) of data exported on frequent basis.

If you worry that you need to pass raw queries for it, do not worry. We have got you covered :). You can pass the query builder and it will do  the rest internally without causing you an extra overhead. 

## Installation

Install via composer
```bash
composer require techsemicolon/laravel-query-to-csv
```

**Register the service Provider**

Add service provider to `config/app.php` in `providers` section
```php
Laravelquerytocsv\QueryToCsvServiceProvider::class,
```

**Set up package configuration**

After executing following command, you will have querytocsv.php in `config` folder
```bash
php artisan vendor:publish
```

## Configurations

The package can be configured from `config/querytocsv.php`

1. 'default-folder' => 'csvexport' 

Takes a string which will be the default folder name inside storage folder, where exports will be kept. This can be changed dynamically for every export as explained in further usase section
2. 'add-timestamp' => true 

Take a boolean. If set to true, it will add timestamp to  the csv file name you have specified, if set to false it won't

3. 'enable-logging' => true

It's suggested to set this to true in development environments OR on production if you really need it. When set to true, it will add a log entry in following format for each export in log file `storage/logs/querytocsv.log`

[2017-12-24 13:38:48] local.INFO: Export Execution Time :  1556.09 milliseconds | File : /storage/csvexport/employees1514122727.csv  

4. 'addlog-row-count' => true

It's suggested to set this to true in development environments OR on production if you really need it. When set to true, it will add the count of rows for each export. This will be useful only when 'enable-logging' is set to true.

[2017-12-24 13:38:48] local.INFO: Export Execution Time :  1556.09 milliseconds | File : /storage/csvexport/employees1514122727.csv | Row Count : 50433

## Usage

1. Using Eloquent for export : 

```php

// Using eloquent query to get the select with eloquent builder. It's not required to do ->get() on the builder instance
// Note : This will not work on eger loaded eloquent relationships. For that You can use DB facade which is explained in next point 2.
$builder = Employees::select('id','name', 'designation','salary');
// Initialize QueryToCsv with Query Builder
$exportObj = QueryToCsv::setQueryBuilder($builder);
// Set csv export path
// If folder is not mentioned, it will be looked up from "default-folder" configration specified in config/querytocsv.php
$fileName = 'users'; //Required | No need to add .csv extension
$folderName = 'csv-export'; //Optional | No need to add slashes before or after
$exportObj->setExportFile($fileName, $folderName);
//Set column headers for export csv | It should match the order and count of columns selected in query builder ->select()
$exportObj->setColumnHeaders([
    'Employee Id',
    'Full Name',
    'Designation',
    'Anual Salary'
]);
// This will generate and download the CSV directly in response from controller
return $exportObj->downloadSheetAsResponse();

//OR if you do not want to download the csv in response, but just generate the csv and get the file path, you can use following instead of ->downloadSheetAsResponse()
$filePath = $exportObj->generateSheetAndGetFilePath();

```

2. Using DB Facade : 

```php

// You can use DB facade instead of Eloquent like below | It's not required to do ->get() on the builder instance
$builder = DB::table('employees')->select('id','name', 'designation','salary')
// Initialize QueryToCsv with Query Builder
$exportObj = QueryToCsv::setQueryBuilder($builder);
// Set csv export path
// If folder is not mentioned, it will be looked up from "default-folder" configration specified in app/config/querytocsv.php
$fileName = 'users'; //Required | No need to add .csv extension
$folderName = 'csv-export'; //Optional | No need to add slashes before or after
$exportObj->setExportFile($fileName, $folderName);
//Set column headers for export csv | It should match the order and count of columns selected in query builder ->select()
$exportObj->setColumnHeaders([
    'Employee Id',
    'Full Name',
    'Designation',
    'Anual Salary'
]);
// This will generate and download the CSV directly in response from controller
return $exportObj->downloadSheetAsResponse();

//OR if you do not want to download the csv in response, but just generate the csv and get the file path, you can use following instead of ->downloadSheetAsResponse()
$filePath = $exportObj->generateSheetAndGetFilePath();

```

3. Using RAW Query : 

```php

// Raw queries are not suggested to be used, but the option is available if anyone specifically needs it
// The package internally checks for common SQL injections performed
$rawQuery = "SELECT `id`,`name`, `designation`,`salary` FROM employees ORDER BY `name` DESC";
// Initialize QueryToCsv with Query Builder
$exportObj = LargeExport::setRawQuery($rawQuery);
// Set csv export path
// If folder is not mentioned, it will be looked up from "default-folder" configration specified in app/config/querytocsv.php
$fileName = 'users'; //Required | No need to add .csv extension
$folderName = 'csv-export'; //Optional | No need to add slashes before or after
$exportObj->setExportFile($fileName, $folderName);
//Set column headers for export csv | It should match the order and count of columns selected in query builder ->select()
$exportObj->setColumnHeaders([
    'Employee Id',
    'Full Name',
    'Designation',
    'Anual Salary'
]);
// This will generate and download the CSV directly in response from controller
return $exportObj->downloadSheetAsResponse();

//OR if you do not want to download the csv in response, but just generate the csv and get the file path, you can use following instead of ->downloadSheetAsResponse()
$filePath = $exportObj->generateSheetAndGetFilePath();

```

## Security

We have got you covered from common SQL Injections if you use setRawQuery() method. 
However if you still discover any issue related to security, please connect back at tech.semicolon@gmail.com

## Credits

- [Mihir Bhende](https://github.com/techsemicolon)
