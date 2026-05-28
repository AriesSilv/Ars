<?php
namespace Ars\Libraries\Database\QB;
trait Write {

    /*
    |--------------------------------------------------------------------------
    | SET
    |--------------------------------------------------------------------------
    */

    public function set(
        $key,
        $value = '',
        $escape = null
    ){

        /*
        |--------------------------------------------------------------------------
        | ARRAY SUPPORT
        |--------------------------------------------------------------------------
        */

        if (is_array($key)) {

            foreach ($key as $k => $v) {

                $this->set($k, $v);

            }

            return $this;
        }

        /*
        |--------------------------------------------------------------------------
        | PARAM
        |--------------------------------------------------------------------------
        */

        $param =
            'set_' .
            str_replace(
                ['.', ' '],
                '_',
                $key
            ) .
            count($this->bindings);

        /*
        |--------------------------------------------------------------------------
        | SET SQL
        |--------------------------------------------------------------------------
        */

        $this->set[] =
            "`{$key}` = :{$param}";

        /*
        |--------------------------------------------------------------------------
        | BINDING
        |--------------------------------------------------------------------------
        */

        $this->bindings[$param] =
            $value;

        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | INSERT
    |--------------------------------------------------------------------------
    */

    public function insert(
        $table = '',
        $set = null
    ){

        $table =
            $table ?: $this->table;

        if ($set) {

            $this->set($set);
        }

        $sql =
            $this->get_compiled_insert(
                $table,
                false
            );

        $this->query($sql);

        /*
        |--------------------------------------------------------------------------
        | BIND
        |--------------------------------------------------------------------------
        */

        foreach ($this->bindings as $k => $v) {

            $this->bind($k, $v);

        }

        $result =
            $this->execute();

        $this->resetQB();

        return $result;
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(
        $table = '',
        $set = null,
        $where = null
    ){

        $table =
            $table ?: $this->table;

        if ($set) {

            $this->set($set);
        }

        if ($where) {

            $this->where($where);
        }

        $sql =
            $this->get_compiled_update(
                $table,
                false
            );

        $this->query($sql);

        /*
        |--------------------------------------------------------------------------
        | BIND
        |--------------------------------------------------------------------------
        */

        foreach ($this->bindings as $k => $v) {

            $this->bind($k, $v);

        }

        $result =
            $this->execute();

        $this->resetQB();

        return $result;
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */

    public function delete(
        $table = '',
        $where = null
    ){

        $table =
            $table ?: $this->table;

        if ($where) {

            $this->where($where);
        }

        $sql =
            $this->get_compiled_delete(
                $table,
                false
            );

        $this->query($sql);

        /*
        |--------------------------------------------------------------------------
        | BIND
        |--------------------------------------------------------------------------
        */

        foreach ($this->bindings as $k => $v) {

            $this->bind($k, $v);

        }

        $result =
            $this->execute();

        $this->resetQB();

        return $result;
    }

    /*
    |--------------------------------------------------------------------------
    | TRUNCATE
    |--------------------------------------------------------------------------
    */

    public function truncate(
        $table = ''
    ){

        $table =
            $table ?: $this->table;

        $this->query(
            "TRUNCATE `{$table}`"
        );

        $result =
            $this->execute();

        $this->resetQB();

        return $result;
    }

    /*
    |--------------------------------------------------------------------------
    | EMPTY TABLE
    |--------------------------------------------------------------------------
    */

    public function empty_table(
        $table = ''
    ){

        $table =
            $table ?: $this->table;

        $this->query(
            "DELETE FROM `{$table}`"
        );

        $result =
            $this->execute();

        $this->resetQB();

        return $result;
    }

    /*
    |--------------------------------------------------------------------------
    | REPLACE
    |--------------------------------------------------------------------------
    */

    public function replace(
        $table = '',
        $set = null
    ){

        $table =
            $table ?: $this->table;

        if ($set) {

            $this->set($set);
        }

        $fields = [];

        $params = [];

        /*
        |--------------------------------------------------------------------------
        | BUILD FIELD
        |--------------------------------------------------------------------------
        */

        foreach ($this->set as $setSql) {

            preg_match(
                '/`(.+?)`/',
                $setSql,
                $fieldMatch
            );

            preg_match(
                '/:(\w+)/',
                $setSql,
                $paramMatch
            );

            $field =
                $fieldMatch[1] ?? null;

            $param =
                $paramMatch[1] ?? null;

            if (!$field || !$param) {
                continue;
            }

            $fields[] =
                "`{$field}`";

            $params[] =
                ":{$param}";
        }

        /*
        |--------------------------------------------------------------------------
        | SQL
        |--------------------------------------------------------------------------
        */

        $sql =
            "REPLACE INTO `{$table}` ("
            . implode(",", $fields)
            . ") VALUES ("
            . implode(",", $params)
            . ")";

        $this->query($sql);

        /*
        |--------------------------------------------------------------------------
        | BIND
        |--------------------------------------------------------------------------
        */

        foreach ($this->bindings as $k => $v) {

            $this->bind($k, $v);

        }

        $result =
            $this->execute();

        $this->resetQB();

        return $result;
    }

}