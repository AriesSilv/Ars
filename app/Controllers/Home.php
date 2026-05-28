<?php
namespace Ars\Controllers;
use Ars\Core\Ars;
class Home extends Ars{
    public function index(){
        $this->load->database();
        $database = $this->db->list_databases();
        var_dump($database);
        echo "Home/Index";
    }
}