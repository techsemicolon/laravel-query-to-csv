<?php 

namespace Laravelquerytocsv;

use Illuminate\Support\ServiceProvider;

class QueryToCsvServiceProvider extends ServiceProvider
{

    /**
     * Provider boot 
     */
    public function boot(){
        
    }
    
    /**
     * Register the publishable configurations
     */
    public function register(){

        $this->publishes([
            __DIR__.'/config/querytocsv.php' => config_path('querytocsv.php'),
        ]);
    }
}