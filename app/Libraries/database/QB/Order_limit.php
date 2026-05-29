<?php
namespace Ars\Libraries\Database\QB;

trait Order_limit {

    /*
    |--------------------------------------------------------------------------
    | ORDER BY
    |--------------------------------------------------------------------------
    */

    public function order_by(
        $orderby,
        $direction = '',
        $escape = null
    ){

        $orders = [];

        /*
        |--------------------------------------------------------------------------
        | ARRAY ORDER
        |--------------------------------------------------------------------------
        */

        if (is_array($orderby)) {

            foreach ($orderby as $key => $val) {

                /*
                |--------------------------------------------------------------------------
                | RAW STRING
                |--------------------------------------------------------------------------
                */

                if (is_numeric($key)) {

                    $orders[] =
                        $this->protect_identifier(
                            trim($val)
                        );

                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | FIELD + DIRECTION
                |--------------------------------------------------------------------------
                */

                $dir =
                    strtoupper(trim($val));

                if (
                    !in_array(
                        $dir,
                        ['ASC', 'DESC']
                    )
                ) {
                    $dir = 'ASC';
                }

                $orders[] =
                    $this->protect_identifier($key)
                    . " {$dir}";
            }

        } else {

            /*
            |--------------------------------------------------------------------------
            | SINGLE ORDER
            |--------------------------------------------------------------------------
            */

            $direction =
                strtoupper(trim($direction));

            if (
                !in_array(
                    $direction,
                    ['ASC', 'DESC']
                )
            ) {
                $direction = 'ASC';
            }

            $orders[] =
                $this->protect_identifier($orderby)
                . " {$direction}";
        }

        /*
        |--------------------------------------------------------------------------
        | APPEND ORDER
        |--------------------------------------------------------------------------
        */

        if (!empty($this->order)) {

            $current =
                preg_replace(
                    '/^ORDER BY\s+/i',
                    '',
                    $this->order
                );

            $this->order =
                "ORDER BY "
                . $current
                . ", "
                . implode(', ', $orders);

        } else {

            $this->order =
                "ORDER BY "
                . implode(', ', $orders);
        }

        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | LIMIT
    |--------------------------------------------------------------------------
    */

    public function limit($limit, $offset = null){

        $this->limit = (int) $limit;
    
        if ($offset !== null) {
            $this->offset = (int) $offset;
        }
    
        return $this;
    }
    /*
    |--------------------------------------------------------------------------
    | OFFSET
    |--------------------------------------------------------------------------
    */

    public function offset($offset){
    
        $this->offset = (int) $offset;
    
        return $this;
    }

}