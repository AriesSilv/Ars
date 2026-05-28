<?php
namespace Ars\Libraries\Database\QB;
trait Transaction {

    /*
    |--------------------------------------------------------------------------
    | TRANSACTION BEGIN
    |--------------------------------------------------------------------------
    */

    public function trans_begin(){

        return $this->dbh->beginTransaction();
    }

    /*
    |--------------------------------------------------------------------------
    | TRANSACTION COMMIT
    |--------------------------------------------------------------------------
    */

    public function trans_commit(){

        return $this->dbh->commit();
    }

    /*
    |--------------------------------------------------------------------------
    | TRANSACTION ROLLBACK
    |--------------------------------------------------------------------------
    */

    public function trans_rollback(){

        return $this->dbh->rollBack();
    }

    /*
    |--------------------------------------------------------------------------
    | TRANSACTION STATUS
    |--------------------------------------------------------------------------
    */

    public function trans_status(){

        return $this->dbh->inTransaction();
    }

    /*
    |--------------------------------------------------------------------------
    | TRANSACTION
    |--------------------------------------------------------------------------
    */

    public function transaction(
        callable $callback
    ){

        try {

            $this->trans_begin();

            $callback($this);

            $this->trans_commit();

            return true;

        } catch (\Throwable $e) {

            if ($this->trans_status()) {

                $this->trans_rollback();
            }

            throw $e;
        }
    }

}