<?php
namespace Ars\Libraries\Database\QB;
trait Result {

    /*
    |--------------------------------------------------------------------------
    | RESULT
    |--------------------------------------------------------------------------
    */

    public function result(
        $type = 'object'
    ){

        $data =
            $this->fetchAll();

        /*
        |--------------------------------------------------------------------------
        | ARRAY
        |--------------------------------------------------------------------------
        */

        if ($type === 'array') {

            return $data;
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

            return $this->mapToClass(
                $data,
                $type
            );
        }

        /*
        |--------------------------------------------------------------------------
        | OBJECT
        |--------------------------------------------------------------------------
        */

        $result = [];

        foreach ($data as $row) {

            $result[] =
                (object) $row;
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
    | RESULT OBJECT
    |--------------------------------------------------------------------------
    */

    public function result_object(){

        return $this->result('object');
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

        $data =
            $this->fetchAll();

        if (!isset($data[$n])) {

            return null;
        }

        $row =
            $data[$n];

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
    | UNBUFFERED ROW
    |--------------------------------------------------------------------------
    */

    public function unbuffered_row(
        $type = 'object'
    ){

        $row =
            $this->stmt->fetch(
                PDO::FETCH_ASSOC
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
    | ROW ARRAY
    |--------------------------------------------------------------------------
    */

    public function row_array(
        $n = 0
    ){

        return $this->row(
            $n,
            'array'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | ROW OBJECT
    |--------------------------------------------------------------------------
    */

    public function row_object(
        $n = 0
    ){

        return $this->row(
            $n,
            'object'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CUSTOM ROW OBJECT
    |--------------------------------------------------------------------------
    */

    public function custom_row_object(
        $n,
        $type
    ){

        return $this->row(
            $n,
            $type
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

        $this->resultIndex =
            (int) $n;

        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | SET ROW
    |--------------------------------------------------------------------------
    */

    public function set_row(
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

    public function previous_row(
        $type = 'object'
    ){

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

        $data =
            $this->fetchAll();

        $index =
            count($data) - 1;

        return $this->row(
            $index,
            $type
        );
    }

    /*
    |--------------------------------------------------------------------------
    | NUM ROWS
    |--------------------------------------------------------------------------
    */

    public function num_rows(){

        return count(
            $this->fetchAll()
        );
    }

    /*
    |--------------------------------------------------------------------------
    | NUM FIELDS
    |--------------------------------------------------------------------------
    */

    public function num_fields(){

        return $this->stmt->columnCount();
    }

    /*
    |--------------------------------------------------------------------------
    | FIELD DATA
    |--------------------------------------------------------------------------
    */

    public function field_data(){

        $fields = [];

        $count =
            $this->stmt->columnCount();

        for ($i = 0; $i < $count; $i++) {

            $meta =
                $this->stmt->getColumnMeta($i);

            $obj = new stdClass;

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
    | LIST FIELDS
    |--------------------------------------------------------------------------
    */

    public function list_fields(){

        $fields = [];

        $count =
            $this->stmt->columnCount();

        for ($i = 0; $i < $count; $i++) {

            $meta =
                $this->stmt->getColumnMeta($i);

            $fields[] =
                $meta['name'] ?? '';
        }

        return $fields;
    }

    /*
    |--------------------------------------------------------------------------
    | FREE RESULT
    |--------------------------------------------------------------------------
    */

    public function free_result(){

        $this->resultCache = null;

        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | FETCH ALL
    |--------------------------------------------------------------------------
    */

    private function fetchAll(){

        if ($this->resultCache === null) {

            $this->resultCache =
                $this->stmt->fetchAll(
                    PDO::FETCH_ASSOC
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
    | MAP TO CLASS
    |--------------------------------------------------------------------------
    */

    private function mapToClass(
        $data,
        $class
    ){

        $result = [];

        foreach ($data as $row) {

            $result[] =
                $this->mapSingleToClass(
                    $row,
                    $class
                );
        }

        return $result;
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

        $obj =
            new $class;

        foreach ($row as $key => $val) {

            $obj->$key = $val;
        }

        return $obj;
    }

}