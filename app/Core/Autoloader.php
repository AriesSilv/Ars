<?php
namespace Ars\Core;
class Autoloader{
    private $load;
    private $library = [];
    private $model = [];
    private $helper = [];
    
    public function __construct($load){
        $this->load = $load;
        $this->init();
    }
    private function init(){
        
        $config = $this->load->config("Autoload");
        $this->library = $config->library;
        $this->helper = $config->helper;
        $this->model = $config->model;
        $this->library();
        $this->helper();
        $this->model();
    }
    private function library(){
        if (!empty($this->library)) {
            foreach ($this->library as $library) {
                if (strtolower($library) == "database") {
                    $this->load->database();
                }elseif (strtolower($library) == "dbutil") {
                    $this->load->dbutil();
                }else{
                $this->load->library($library);
                }
            }
        }
    }
    private function helper(){
        if (!empty($this->helper)) {
            foreach ($this->helper as $helper) {
                $this->load->helper($helper);
            }
        }
    }
    private function model(){
        if (!empty($this->model)) {
            foreach ($this->model as $model) {
                $this->load->model($model);
            }
        }
    }
}