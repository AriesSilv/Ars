<?php
namespace Ars\Libraries\Database\QB;
trait Result {

    /*
    |--------------------------------------------------------------------------
    | RESULT
    |--------------------------------------------------------------------------
    */

    public function result(
    $type = 'object'){

        $data = $this->fetchAll();
    
        $result = [];
    
        foreach ($data as $row) {
    
            $result[] =
                $this->convertType(
                    $row,
                    $type
                );
        }
    
        return $result;
    }

    /*
    |--------------------------------------------------------------------------
    | RESULT ARRAY
    |--------------------------------------------------------------------------
    */

    public function result_array(){

        return $this->result('array');
    }



    /*
    |--------------------------------------------------------------------------
    | CUSTOM RESULT OBJECT
    |--------------------------------------------------------------------------
    */

    public function custom_result_object(
        $class_name
    ){

        return $this->result(
            $class_name
        );
    }

    /*
    |--------------------------------------------------------------------------
    | ROW
    |--------------------------------------------------------------------------
    */

    public function row(
        $n = 0,
        $type = 'object'
    ){
    
        $data = $this->fetchAll();
    
        if (!isset($data[$n])) {
            return null;
        }
    
        return $this->convertType(
            $data[$n],
            $type
        );
    }
    /*
    |--------------------------------------------------------------------------
    | ROW ARRAY
    |--------------------------------------------------------------------------
    */

    public function row_array(){

        return $this->row(0,'array');
    }
    
    /*
    |--------------------------------------------------------------------------
    | CUSTOM ROW OBJECT
    |--------------------------------------------------------------------------
    */

    public function custom_row_object(
        $class_name
    ){

        return $this->row(
            0,$class_name
        );
    }

    /*
    |--------------------------------------------------------------------------
    | UNBUFFERED ROW
    |--------------------------------------------------------------------------
    */

    public function unbuffered_row(
        $type = 'object'
    ){
    
        if (!$this->stmt) {
            return null;
        }
    
        if ($this->resultCache !== null) {
    
            throw new \Exception(
                'Cannot use unbuffered_row() after buffered result'
            );
        }
    
        $this->resultMode = 'unbuffered';
    
        $row =
            $this->stmt->fetch(
                \PDO::FETCH_ASSOC
            );
    
        if (!$row) {
            return null;
        }
    
        return $this->convertType(
            $row,
            $type
        );
    }
    
    /*
    |--------------------------------------------------------------------------
    | num_rows
    |--------------------------------------------------------------------------
    */

    public function num_rows(){

        if (!$this->stmt) {
            return 0;
        }
    
        /*
        |--------------------------------------------------------------------------
        | WRITE QUERY
        |--------------------------------------------------------------------------
        */
    
        if ($this->queryType === 'write') {
    
            return $this->stmt->rowCount();
        }
    
        /*
        |--------------------------------------------------------------------------
        | UNBUFFERED
        |--------------------------------------------------------------------------
        */
    
        if ($this->resultMode === 'unbuffered') {
    
            throw new \Exception(
                'num_rows() unavailable in unbuffered mode'
            );
        }
    
        /*
        |--------------------------------------------------------------------------
        | BUFFERED SELECT
        |--------------------------------------------------------------------------
        */
    
        return count(
            $this->fetchAll()
        );
    }

    /*
    |--------------------------------------------------------------------------
    | DATA SEEK
    |--------------------------------------------------------------------------
    */

    public function data_seek(
        $n = 0
    ){
        if ($this->resultMode === 'unbuffered') {
            throw new \Exception(
            "data_seek() unavailable in unbuffered mode"
        );
        }
        $this->resultIndex =
            (int) $n;

        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE ROW CACHE
    |--------------------------------------------------------------------------
    */

    public function update_row_cache(
        $key,
        $value = null
    ){

        $data =
            $this->fetchAll();

        if (
            !isset(
                $data[$this->resultIndex]
            )
        ) {

            return $this;
        }

        if (is_array($key)) {

            foreach ($key as $k => $v) {

                $data[$this->resultIndex][$k] = $v;
            }

        } else {

            $data[$this->resultIndex][$key] =
                $value;
        }

        $this->resultCache =
            $data;

        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | NEXT ROW
    |--------------------------------------------------------------------------
    */

    public function next_row(
        $type = 'object'
    ){
        if ($this->resultMode === 'unbuffered') {
            throw new \Exception(
        "next_row() unavailable in unbuffered mode"
        );
        }
        
        $this->resultIndex++;

        return $this->row(
            $this->resultIndex,
            $type
        );
    }

    /*
    |--------------------------------------------------------------------------
    | PREVIOUS ROW
    |--------------------------------------------------------------------------
    */

    public function prev_row(
        $type = 'object'
    ){
        if ($this->resultMode === 'unbuffered') {
            throw new \Exception(
                "prev_row() unavailable in unbuffered mode"
            );
        }
        
        if ($this->resultIndex > 0) {

            $this->resultIndex--;
        }

        return $this->row(
            $this->resultIndex,
            $type
        );
    }

    /*
    |--------------------------------------------------------------------------
    | FIRST ROW
    |--------------------------------------------------------------------------
    */

    public function first_row(
        $type = 'object'
    ){
        if ($this->resultMode === 'unbuffered') {
            throw new \Exception(
                "first_row() unavailable in unbuffered mode"
            );
        }
        return $this->row(
            0,
            $type
        );
    }

    /*
    |--------------------------------------------------------------------------
    | LAST ROW
    |--------------------------------------------------------------------------
    */

     public function last_row(
        $type = 'object'
    ){
    
        if ($this->resultMode === 'unbuffered') {
    
            throw new \Exception(
                "last_row() unavailable in unbuffered mode"
            );
        }
    
        $data = $this->fetchAll();
    
        if (empty($data)) {
            return null;
        }
    
        $index =
            count($data) - 1;
    
        return $this->row(
            $index,
            $type
        );
    }
    /*
    |--------------------------------------------------------------------------
    | NUM FIELDS
    |--------------------------------------------------------------------------
    */

    public function num_fields(){
        if (!$this->stmt) {
            return 0;
        }
        return $this->stmt->columnCount();
    }

    /*
    |--------------------------------------------------------------------------
    | FIELD DATA
    |--------------------------------------------------------------------------
    */

    public function field_data(){
    
        $fields = [];
    
        if (!$this->stmt) {
            return $fields;
        }
    
        $count =
            $this->stmt->columnCount();
    
        for ($i = 0; $i < $count; $i++) {
    
            $meta =
                $this->stmt->getColumnMeta($i);
    
            if (!$meta) {
                continue;
            }
    
            $obj = new \stdClass;
    
            $obj->name =
                $meta['name'] ?? '';
    
            $obj->type =
                $meta['native_type'] ?? '';
    
            $obj->max_length =
                $meta['len'] ?? null;
    
            $obj->primary_key = false;
    
            $obj->default = null;
    
            $fields[] = $obj;
        }
    
        return $fields;
    }


    /*
    |--------------------------------------------------------------------------
    | FETCH ALL
    |--------------------------------------------------------------------------
    */

    private function fetchAll(){

        if (!$this->stmt) {
            return [];
        }

        /*
        |--------------------------------------------------------------------------
        | UNBUFFERED PROTECTION
        |--------------------------------------------------------------------------
        */

        if ($this->resultMode === 'unbuffered') {
            throw new \Exception(
                "Cannot use fetchAll() after unbuffered_row()"
            );
        }

        /*
        |--------------------------------------------------------------------------
        | CACHE
        |--------------------------------------------------------------------------
        */
    
        if ($this->resultCache === null) {
            $this->resultCache =
                $this->stmt->fetchAll(
                    \PDO::FETCH_ASSOC
                );
        }
    
        return $this->resultCache;
    }

    /*
    |--------------------------------------------------------------------------
    | CONVERT TYPE
    |--------------------------------------------------------------------------
    */

    private function convertType(
        $row,
        $type
    ){

        if (!$row) {

            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | ARRAY
        |--------------------------------------------------------------------------
        */

        if ($type === 'array') {

            return $row;
        }

        /*
        |--------------------------------------------------------------------------
        | CUSTOM CLASS
        |--------------------------------------------------------------------------
        */

        if (
            is_string($type)
            &&
            class_exists($type)
        ) {

            return $this->mapSingleToClass(
                $row,
                $type
            );
        }

        /*
        |--------------------------------------------------------------------------
        | OBJECT
        |--------------------------------------------------------------------------
        */

        return (object)
            $row;
    }

    /*
    |--------------------------------------------------------------------------
    | MAP SINGLE TO CLASS
    |--------------------------------------------------------------------------
    */

    private function mapSingleToClass(
        $row,
        $class
    ){
    
        $obj = new $class;
    
        $ref =
            new \ReflectionObject($obj);
    
        foreach ($row as $key => $val) {
    
            if (!$ref->hasProperty($key)) {
                continue;
            }
    
            $prop =
                $ref->getProperty($key);
    
            if ($prop->isReadOnly()) {
                continue;
            }
    
            $obj->$key = $val;
        }
    
        return $obj;
    }
}