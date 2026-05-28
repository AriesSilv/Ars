<?php
namespace Ars\Libraries\Database\QB;
trait Cache {

    /*
    |--------------------------------------------------------------------------
    | START CACHE
    |--------------------------------------------------------------------------
    */

    public function start_cache(){

        $this->qb_cache = true;

        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | STOP CACHE
    |--------------------------------------------------------------------------
    */

    public function stop_cache(){

        $this->qb_cache = false;

        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | FLUSH CACHE
    |--------------------------------------------------------------------------
    */

    public function flush_cache(){

        $this->qb_cache_data = [
            'select' => [],
            'where' => [],
            'join' => [],
            'groupBy' => [],
            'having' => [],
            'order' => [],
        ];

        return $this;
    }

}