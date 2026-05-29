<?php
namespace Ars\Libraries\Database\QB;

use PDO;

trait Query_builder  {

    /*
    |--------------------------------------------------------------------------
    | TRAITS
    |--------------------------------------------------------------------------
    */

    use Select;
    use Where;
    use Like;
    use Join;
    use Group;
    use Having;
    use Order_limit;
    use Compile;
    use Get;
    use Result;
    use Write;
    use Cache;
    use Prefix;
    use Transaction;
    use Utility;



    /*
    |--------------------------------------------------------------------------
    | QUERY BUILDER STATE
    |--------------------------------------------------------------------------
    */

    protected $table;

    protected $select = [];

    protected $distinct = false;

    protected $where = [];

    protected $bindings = [];

    protected $join = [];

    protected $set = [];

    protected $groupBy = [];

    protected $having = [];

    protected $groupStack = [];

    protected $order = '';

    protected $limit = '';

    protected $offset = '';
    
    protected $resultMode = 'buffered';
    protected $lastQuery = '';
    protected $queryType = 'read';

    /*
    |--------------------------------------------------------------------------
    | CACHE
    |--------------------------------------------------------------------------
    */

    protected $qb_cache = false;

    protected $qb_cache_data = [
        'select'   => [],
        'where'    => [],
        'join'     => [],
        'groupBy'  => [],
        'having'   => [],
        'order'    => [],
    ];

    /*
    |--------------------------------------------------------------------------
    | PREFIX
    |--------------------------------------------------------------------------
    */

    protected $dbprefix = '';

    /*
    |--------------------------------------------------------------------------
    | BATCH
    |--------------------------------------------------------------------------
    */

    protected $insertBatch = [];

    protected $updateBatch = [];

    /*
    |--------------------------------------------------------------------------
    | RESULT CACHE
    |--------------------------------------------------------------------------
    */

    protected $resultCache = null;

    protected $resultIndex = 0;



    /*
    |--------------------------------------------------------------------------
    | PREPARE QUERY
    |--------------------------------------------------------------------------
    */

    public function query($query){
    
        $this->reset_result();
    
        /*
        |--------------------------------------------------------------------------
        | QUERY TYPE
        |--------------------------------------------------------------------------
        */
    
        $sqlType =
            strtoupper(
                strtok(
                    trim($query),
                    ' '
                )
            );
    
        $this->queryType =
            in_array(
                $sqlType,
                ['SELECT', 'SHOW']
            )
            ? 'read'
            : 'write';
    
        /*
        |--------------------------------------------------------------------------
        | PREPARE
        |--------------------------------------------------------------------------
        */
    
        $stmt =
            $this->dbh->prepare($query);
    
        if (!$stmt) {
    
            throw new \RuntimeException(
                'Failed to prepare query'
            );
        }
    
        $this->stmt = $stmt;
    
        $this->lastQuery = $query;
    
        return $this;
    }
    /*
    |--------------------------------------------------------------------------
    | LAST QUERY
    |--------------------------------------------------------------------------
    */
    
    public function last_query(){
        return $this->lastQuery;
    }

    /*
    |--------------------------------------------------------------------------
    | BIND PARAM
    |--------------------------------------------------------------------------
    */

    public function bind(
        $param,
        $value,
        $type = null
    ){
    
        if (!is_string($param) || $param === '') {
    
            throw new \InvalidArgumentException(
                'Invalid parameter bind'
            );
        }
    
        if (is_null($type)) {
    
            switch (true) {
    
                case is_int($value):
    
                    $type = PDO::PARAM_INT;
                    break;
    
                case is_bool($value):
    
                    $type = PDO::PARAM_BOOL;
                    break;
    
                case is_null($value):
    
                    $type = PDO::PARAM_NULL;
                    break;
    
                default:
    
                    $type = PDO::PARAM_STR;
                    break;
            }
        }
    
        /*
        |--------------------------------------------------------------------------
        | AUTO :
        |--------------------------------------------------------------------------
        */
    
        if (!str_starts_with($param, ':')) {
    
            $param = ":{$param}";
        }
    
        $this->stmt->bindValue(
            $param,
            $value,
            $type
        );
    
        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | EXECUTE
    |--------------------------------------------------------------------------
    */

    public function execute(){

        if (!$this->stmt) {
            return false;
        }
        return $this->stmt->execute();
    }

    /*
    |--------------------------------------------------------------------------
    | LAST INSERT ID
    |--------------------------------------------------------------------------
    */

    public function insert_id(){

        return $this->dbh->lastInsertId();
    }
    /*
    |--------------------------------------------------------------------------
    | FREE RESULT
    |--------------------------------------------------------------------------
    */

    public function reset_result(){

        $this->resultCache = null;
        $this->resultIndex = 0;
        $this->resultMode = 'buffered';
        return $this;
    }
    /*
    |--------------------------------------------------------------------------
    | RESET QUERY BUILDER
    |--------------------------------------------------------------------------
    */

    public function reset_qb(){
    
        $this->table = null;
    
        $this->select = ["*"];
    
        $this->distinct = false;
    
        $this->where = [];
    
        $this->join = [];
    
        $this->set = [];
    
        $this->bindings = [];
    
        $this->groupBy = [];
    
        $this->having = [];
    
        $this->groupStack = [];
    
        $this->order = '';
    
        $this->limit = '';
    
        $this->offset = '';
    
        $this->queryType = 'read';
    
        return $this;
    }

}