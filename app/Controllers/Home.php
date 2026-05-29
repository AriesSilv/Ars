<?php
namespace Ars\Controllers;
use Ars\Core\Ars;
class Home extends Ars{
    public function index(){
         $data = [
    [
        'id'   => 1,
        'name' => 'Uncharted'
    ],
    [
        'id'   => 2,
        'name' => 'GTA VI'
    ]
];
$this->load->database();
    $q = $this->db
        ->table('produk')
        ->set($data)
        ->get_compiled_update_batch(
            'produk',
            'id'
        );

    var_dump($q);

    var_dump(
        $this->db->bindings);
        
    }
}