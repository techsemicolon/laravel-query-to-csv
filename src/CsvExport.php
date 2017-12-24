<?php

namespace Laravelquerytocsv;
use DB;
use Log;
use File;
use Config;

class CsvExport
{
    // Initializing class private properties
    private $exportFilePath = null;
    private $rawSql = null;
    private $exportColumnHeaders = [];
    private $type = null;
    private $rowcount = null;

    /**
     * Constructor for CsvExport Instance
     *
     * @param Object OR String $builder, Boolean $isRawQuery
     * 
     * @return CurrentInstance
     */
    public function __construct($builder, $isRawQuery = false){

        if($isRawQuery){
            $this->type = 'raw'; 
            $this->rawSql = $this->checkForSqlInjection($builder);
        }
        else{
            $this->type = 'builder';
            if(Config::get('querytocsv.enable-logging') && Config::get('querytocsv.addlog-row-count')){
                $this->rowcount = $builder->count();
            }
            $this->rawSql = $this->getSqlWithBinding($builder);
        }
        return $this;
    }

    /**
     * Set Export Location for the CSV
     *
     * @param String $fileName, String $folderName
     * 
     * @return CurrentInstance
     */
    public function setExportFile($fileName, $folderName = null){
        
        $this->exportFilePath = $this->getExportFilePath($fileName, $folderName);
        return $this;
    }
    
    /**
     * Generate sheet and download it as a response
     * 
     * @return Response
     */
    public function downloadSheetAsResponse(){

        $this->generateCsv();
        return response()->download($this->exportFilePath);
    }

    /**
     * Generate sheet and get the file path 
     * 
     * @return String filepath
     */
    public function generateSheetAndGetFilePath(){
        
        $this->generateCsv();
        return $this->exportFilePath;
    }

    /**
     * Generate CSV and Log Details if applicable
     * 
     * @return CurrentInstance
     */
    public function generateCsv(){

        try{
            
            $expotQuery = $this->modifyRawSqlWithOutfileDetails();
            if(Config::get('querytocsv.enable-logging')){
                $time_pre = microtime(true);
            }

            DB::statement($expotQuery);

            if(Config::get('querytocsv.enable-logging')){
                $time_post = microtime(true);
                $exec_time = ($time_post - $time_pre) * 1000;
                $exec_time = round($exec_time, 2);
                Log::useDailyFiles(storage_path().'/logs/querytocsv.log');
                $logPath = str_replace(str_replace('\\','/', base_path()), '', $this->exportFilePath);
                if(Config::get('querytocsv.addlog-row-count')){
                    Log::info("Export Execution Time :  $exec_time milliseconds | File : $logPath | Row Count : $this->rowcount");
                }
                else{
                    Log::info("Export Execution Time :  $exec_time milliseconds | File : $logPath");
                }
            }
            
        }
        catch(\Exception $e){

            dd($e->getMessage());
            $error = strtolower($e->getMessage());
            if (str_contains($error,'cardinality')) {
                abort(500, "LaravelLargeExportException : The count of column you have specified for export does not match with selected columns in MySQL query!");
            }
            abort(500, "LaravelLargeExportException : You have an error in your SELECT query syntax. Query : \" $this->rawSql \"");
        }

        return $this;
    }

    /**
     * Check For SQL Injection
     *
     * @param String $originalQueryString
     *  
     * @return String SanitizedQuery
     */
    private function checkForSqlInjection($originalQueryString){

        $queryString = strtolower(trim($originalQueryString));
        $queryIntoWords = explode(' ', $queryString);
        $queryStatementsAllowed = ['select'];
        $queryStatementFromQueryString = $queryIntoWords[0];
        
        //check for query statements, reject all except SELECT statement
        if(!in_array($queryStatementFromQueryString, $queryStatementsAllowed)){
            abort(500, "LaravelLargeExportException : $queryStatementFromQueryString query is not allowed as you are exporting the data, please use select statements!");
        }

        //check for multiple query statements separated by semicolon
        //remove all bindings because semicolon can be also contained as a search string in bindings
        $queryBindingsRemoved = preg_replace("/(?:(?:\"(?:\\\\\"|[^\"])+\")|(?:'(?:\\\'|[^'])+'))/is", '', $queryString);

        if(strpos(';', $queryBindingsRemoved) !== false){
            abort(500, "LaravelLargeExportException : Query string seems to have multiple queries!");
        }

        return $originalQueryString; 
    }

    /**
     * Generate required query with OUTFILE syntax
     *  
     * @return String $modifiedSql
     */
    private function modifyRawSqlWithOutfileDetails(){
        
        $columnsHeadersCommaSeparated = "'" . implode("', '", $this->exportColumnHeaders) . "'";
        $modifiedSql = "SELECT * FROM(
                    SELECT $columnsHeadersCommaSeparated
                    UNION ALL 
                    $this->rawSql
                ) AS export
                INTO OUTFILE '$this->exportFilePath' 
                FIELDS TERMINATED BY ',' 
                OPTIONALLY ENCLOSED BY '\"'
                LINES TERMINATED BY '\n'
            ";
        return $modifiedSql;
    }

    /**
     * Set column headers
     *
     * @param Array $columnHeaders
     * 
     * @return CurrentInstance
     */
    public function setColumnHeaders(array $columnHeaders){
        
        if(count($columnHeaders) > 0){
            $this->exportColumnHeaders = $columnHeaders;
        }
        return $this;
    }

     /**
     * Get entire query string with bindings from Builder Instance
     *
     * @param Object $queryBuilder
     * 
     * @return String $sql
     */
    private function getSqlWithBinding($queryBuilder) 
    {
        $builder = $queryBuilder;	
        $sql = $builder->toSql();
        foreach($builder->getBindings() as $binding)
        {
            $value = is_numeric($binding) ? $binding : "'".$binding."'";
            $sql = preg_replace('/\?/', $value, $sql, 1);
        }
        return $sql;
    }

    /**
     * Get export file path
     *
     * @param String $fileName, String $folderName
     * 
     * @return String $filePath
     */
    private function getExportFilePath($fileName, $folderName){
       
        $defaultFolder = Config::get('querytocsv.default-folder') ? Config::get('querytocsv.default-folder') : 'csvexport';
        $folderName = $folderName ? $folderName:$defaultFolder;
        $fileName = $fileName ? $fileName:'sheetfile';

        if(Config::get('querytocsv.add-timestamp')){
            $fileName .= time().'.csv';
        }
        else{
            $fileName .= '.csv';
        }
        
        //Delete file if exists
        File::delete(storage_path("$folderName\\$fileName"));
        
        $filePath = storage_path("$folderName\\$fileName");
        $filePath = str_replace('\\','/', $filePath);

        //create folder in storage it does not exist
        if(!File::exists(storage_path("$folderName"))) {
            File::makeDirectory(storage_path("$folderName"));
        }
        return $filePath;
    }

}