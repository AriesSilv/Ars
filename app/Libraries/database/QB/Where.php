<?php
namespace Ars\Libraries\Database\QB;
trait Where {

    /*
    |--------------------------------------------------------------------------
    | ADD WHERE
    |--------------------------------------------------------------------------
    */

    protected function addWhere(
        $condition,
        $boolean = 'AND'
    ){

        if (empty($this->where)) {

            $this->where[] = $condition;

        } else {

            $this->where[] =
                "{$boolean} {$condition}";
        }

        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | WHERE
    |--------------------------------------------------------------------------
    */

    public function where(
        $key = null,
        $value = null,
        $escape = null
    ){

        return $this->buildWhere(
            $key,
            $value,
            'AND',
            $escape
        );
    }

    /*
    |--------------------------------------------------------------------------
    | OR WHERE
    |--------------------------------------------------------------------------
    */

    public function or_where(
        $key = null,
        $value = null,
        $escape = null
    ){

        return $this->buildWhere(
            $key,
            $value,
            'OR',
            $escape
        );
    }

    /*
    |--------------------------------------------------------------------------
    | BUILD WHERE
    |--------------------------------------------------------------------------
    */

    private function buildWhere(
        $key,
        $value,
        $boolean,
        $escape
    ){

        /*
        |--------------------------------------------------------------------------
        | ARRAY SUPPORT
        |--------------------------------------------------------------------------
        */

        if (is_array($key)) {

            foreach ($key as $k => $v) {

                $this->buildWhere(
                    $k,
                    $v,
                    $boolean,
                    $escape
                );

            }

            return $this;
        }

        /*
        |--------------------------------------------------------------------------
        | RAW CONDITION
        |--------------------------------------------------------------------------
        */

        if ($value === null) {

            $this->addWhere(
                $key,
                $boolean
            );

            return $this;
        }

        /*
        |--------------------------------------------------------------------------
        | OPERATOR DETECT
        |--------------------------------------------------------------------------
        */

        if (
            preg_match(
                '/(>=|<=|!=|<>|>|<|LIKE)$/i',
                trim($key)
            )
        ) {

            $field = $key;

        } else {

            $field = "`{$key}` =";

        }

        /*
        |--------------------------------------------------------------------------
        | PARAM
        |--------------------------------------------------------------------------
        */

        $param =
            'where_' .
            str_replace(
                ['.', ' '],
                '_',
                $key
            ) .
            count($this->bindings);

        /*
        |--------------------------------------------------------------------------
        | CONDITION
        |--------------------------------------------------------------------------
        */

        $this->addWhere(
            "{$field} :{$param}",
            $boolean
        );

        /*
        |--------------------------------------------------------------------------
        | BINDING
        |--------------------------------------------------------------------------
        */

        $this->bindings[$param] = $value;

        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | WHERE IN
    |--------------------------------------------------------------------------
    */

    public function where_in(
        $key = null,
        $values = null,
        $escape = null
    ){

        return $this->buildWhereIn(
            $key,
            $values,
            'AND',
            false
        );
    }

    /*
    |--------------------------------------------------------------------------
    | OR WHERE IN
    |--------------------------------------------------------------------------
    */

    public function or_where_in(
        $key = null,
        $values = null,
        $escape = null
    ){

        return $this->buildWhereIn(
            $key,
            $values,
            'OR',
            false
        );
    }

    /*
    |--------------------------------------------------------------------------
    | WHERE NOT IN
    |--------------------------------------------------------------------------
    */

    public function where_not_in(
        $key = null,
        $values = null,
        $escape = null
    ){

        return $this->buildWhereIn(
            $key,
            $values,
            'AND',
            true
        );
    }

    /*
    |--------------------------------------------------------------------------
    | OR WHERE NOT IN
    |--------------------------------------------------------------------------
    */

    public function or_where_not_in(
        $key = null,
        $values = null,
        $escape = null
    ){

        return $this->buildWhereIn(
            $key,
            $values,
            'OR',
            true
        );
    }

    /*
    |--------------------------------------------------------------------------
    | BUILD WHERE IN
    |--------------------------------------------------------------------------
    */

    private function buildWhereIn(
        $key,
        $values,
        $boolean,
        $not
    ){

        if (
            !is_array($values) ||
            empty($values)
        ) {

            return $this;
        }

        $params = [];

        foreach ($values as $i => $val) {

            $param =
                'wherein_' .
                $key .
                '_' .
                count($this->bindings) .
                "_{$i}";

            $params[] = ":{$param}";

            $this->bindings[$param] = $val;
        }

        /*
        |--------------------------------------------------------------------------
        | OPERATOR
        |--------------------------------------------------------------------------
        */

        $operator =
            $not
            ? 'NOT IN'
            : 'IN';

        /*
        |--------------------------------------------------------------------------
        | CONDITION
        |--------------------------------------------------------------------------
        */

        $condition =
            "`{$key}` {$operator} ("
            . implode(",", $params)
            . ")";

        $this->addWhere(
            $condition,
            $boolean
        );

        return $this;
    }

}