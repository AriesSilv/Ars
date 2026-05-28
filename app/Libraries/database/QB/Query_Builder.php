<?php
namespace Ars\Libraries\Database\QB;

use PDO;

trait Query_Builder  {

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
    use Batch;
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

    protected $select = '*';

    protected $distinct = false;

    protected $from = '';

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

        $this->free_result();

        $this->stmt =
            $this->dbh->prepare($query);

        return $this;
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

        if ($param[0] !== ':') {

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

        return $this->stmt->execute();
    }

    /*
    |--------------------------------------------------------------------------
    | ROW COUNT
    |--------------------------------------------------------------------------
    */

    public function rowCount(){

        return $this->stmt->rowCount();
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
    | CLOSE CONNECTION
    |--------------------------------------------------------------------------
    */

    public function close(){

        $this->stmt = null;

        $this->dbh = null;
    }

    /*
    |--------------------------------------------------------------------------
    | RESET QUERY BUILDER
    |--------------------------------------------------------------------------
    */

    public function resetQB(){

        /*
        |--------------------------------------------------------------------------
        | TABLE
        |--------------------------------------------------------------------------
        */

        $this->table = null;

        /*
        |--------------------------------------------------------------------------
        | SELECT
        |--------------------------------------------------------------------------
        */

        $this->select = '*';

        $this->distinct = false;

        $this->from = '';

        /*
        |--------------------------------------------------------------------------
        | QUERY PARTS
        |--------------------------------------------------------------------------
        */

        $this->where = [];

        $this->join = [];

        $this->set = [];

        $this->bindings = [];

        $this->groupBy = [];

        $this->having = [];

        $this->groupStack = [];

        /*
        |--------------------------------------------------------------------------
        | ORDER / LIMIT
        |--------------------------------------------------------------------------
        */

        $this->order = '';

        $this->limit = '';

        $this->offset = '';

        /*
        |--------------------------------------------------------------------------
        | RESULT CACHE
        |--------------------------------------------------------------------------
        */

        $this->resultCache = null;

        $this->resultIndex = 0;

        return $this;
    }

}