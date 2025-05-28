<?php
class ControllerRepairCompareTables extends Controller {
	public function index() {
        $this->load->model('repair/compare');
        
        $data['columns_name'] = $this->model_repair_compare->getColumnName();

        $this->response->setOutput($this->load->view('repair/compare', $data));
	 }
    
    public function getTables() {
        unset($_SESSION['items']);
        $this->load->model('repair/compare');
        $column_name = $this->request->post['column_name'];

        $data['tables'] = $this->model_repair_compare->getTables($column_name);
        $data['primary'] = $this->model_repair_compare->getPrimary($column_name);
        $data['switch'] = 'getTables';
        $this->response->setOutput($this->load->view('repair/compare_tables', $data));

    }
    

    
    
    public function getItems() {

        $this->load->model('repair/compare');
        $column = $this->request->post['column_name'];
        $table = $this->request->post['table_main'];
        $table_compare =  $this->request->post['table_compare'];
        $data['column'] = $column;
        
        if($table_compare != 'Select Table to Compare'){
            $items = $this->model_repair_compare->getItems($column, $table, $table_compare);
        
            if($items != 'empty'){
                $data['items'] = $items;
            } else {
                $data['items'] = ''; 
            }
            
        } else {
            $data['items'] = '';  
        }
        
        
        $this->response->setOutput($this->load->view('repair/compare_items', $data));
    }
    
    
    public function defrag() {
        if(!isset($_SESSION['total'])){
            $_SESSION['total'] = 0;
        }

        if(!isset($_SESSION['total_refresh'])){
            $_SESSION['total_refresh'] = 0;
        }


        if(!isset($_SESSION['info'])){
            $_SESSION['info'] = array();
        } 

        if(!isset($_SESSION['current_key'])){
            $_SESSION['current_key'] =  -1;
        }


        if(!isset($_SESSION['time'])){
            $_SESSION['time'] = time();
        } 
        
        $tables = array();
        
        $data['column'] = $this->request->post['column_name'];
        $data['table_main'] = $this->request->post['table_main'];
        $tables[] = $this->request->post['table_name'];


        $this->load->model('repair/defragmentation');


        $data['current'] = $_SESSION['items'][$_SESSION['current_key']];

        $data['info_last_id'][1] = 0;

        if(count($_SESSION['info']) >= 1){
            preg_match("/id='(.*?)'/", html_entity_decode(end($_SESSION['info'])), $data['info_last_id']);
        }

        if($data['current'] != $data['info_last_id'][1] && $data['info_last_id'][1] >= 1){
            $_SESSION['current_key'] = $_SESSION['current_key'] - 1;
            array_pop($_SESSION['info']);
            $_SESSION['total'] = $_SESSION['total'] + 2; 
            $_SESSION['resending'] = true;
        }else {
            $_SESSION['current_key'] = $_SESSION['current_key'] + 1;
        }


        $result = $this->model_repair_defragmentation->getItem($_SESSION['items'][$_SESSION['current_key']], $data['column'], $data['table_main']);

        $data['items'] = $result['item'];
        //$data['name'] = mb_substr($result['name'], 0, 80, 'UTF-8');
                
          

        $this->response->setOutput($this->load->view('repair/defragmentation_form', $data));
    }
    
    public function stop() {
        $time = time() - $_SESSION['time'];
        $total_wrap = time() - $_SESSION['time'];
        
        echo "<div id='time'> Time: <span>" . gmdate("H:i:s", $time) . "</span></div>";
        echo "<span id='total'>Total:" . $_SESSION['total'] . "&nbsp;&nbsp [0-". $total_wrap . "]&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>";
        
        
        unset($_SESSION['current_key']);
        unset($_SESSION['info']);
        unset($_SESSION['total']);
        unset($_SESSION['refresh']);
        unset($_SESSION['time']);
        unset($_SESSION['total_refresh']);
        unset($_SESSION['product_id']);
        unset($_SESSION['resending']);
        unset($_SESSION['all_key']);
    }
}
