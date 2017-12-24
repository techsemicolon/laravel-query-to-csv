<?php

namespace Laravelquerytocsv;

use Laravelquerytocsv\CsvExport;

class QueryToCsv
{
    
    /**
     * Set builder query and return instance of CsvExport
     *
     * @param Object $builder
     *  
     * @return NewInstance Of CsvExport
     */
    public static function setQueryBuilder($builder){

        if(is_a($builder, 'Illuminate\Database\Query\Builder') || is_a($builder, 'Illuminate\Database\Eloquent\Builder')){
            return new CsvExport($builder);
        }
        $builderClass = get_class($builder);
        abort(500, "LaravelLargeExportException : The paramater passed to setQueryBuilder(\$builder) function has to be an instance of either Illuminate\Database\Query\Builder OR Illuminate\Database\Eloquent\Builder, instance of $builderClass is given!");
        
    }
    
    /**
     * Set raw query and return instance of CsvExport
     *
     * @param String ($rawQuer
     *  
     * @return NewInstance Of CsvExport
     */
    public static function setRawQuery($rawQuery){
        
        return new CsvExport($rawQuery, true);
    }

}