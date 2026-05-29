<?php
namespace Ars\Libraries\Database\QB;
trait Get {

    /*
    |--------------------------------------------------------------------------
    | GET
    |--------------------------------------------------------------------------
    */

    public function get(
        $table = '',
        $limit = null,
        $offset = null
    ){
    
        if ($table) {
    
            $this->table = $table;
        }
    
        if ($limit !== null) {
    
            $this->limit(
                $limit,
                $offset ?? 0
            );
        }
    
        $sql =
            $this->get_compiled_select(
                $this->table
            );
    
        if (!$sql) {
    
            throw new \RuntimeException(
                'Failed compile select query'
            );
        }
        $this->run($sql);
        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | GET WHERE
    |--------------------------------------------------------------------------
    */

    public function get_where(
        $table = '',
        $where = [],
        $limit = null,
        $offset = null
    ){

        if ($table) {

            $this->table = $table;
        }

        /*
        |--------------------------------------------------------------------------
        | WHERE
        |--------------------------------------------------------------------------
        */

        if (!empty($where)) {

            $this->where($where);
        }

        return $this->get(
            $this->table,
            $limit,
            $offset
        );
    }
    public function list_fields(
    $table = ''
    ){
    
        $table =
            $table ?: $this->table;
    
        if (!$table) {
            return [];
        }
    
        $table =
            $this->protect_identifier($table);
    
        $this->query(
            "SHOW COLUMNS FROM {$table}"
        );
    
        $this->execute();
    
        return array_column(
            $this->stmt->fetchAll(
                \PDO::FETCH_ASSOC
            ),
            'Field'
        );
    }
}