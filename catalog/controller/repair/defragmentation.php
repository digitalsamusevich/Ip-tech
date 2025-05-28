<?php
class ControllerRepairDefragmentation extends Controller {
	public function index() {
        $this->load->model('repair/defragmentation');
        
        $data['columns_name'] = $this->model_repair_defragmentation->getColumnName();

        $this->response->setOutput($this->load->view('repair/defragmentation', $data));
	 }
    
    public function getTables() {
        $this->load->model('repair/defragmentation');
        $column_name = $this->request->post['column_name'];
        
        $json = array();
        
        $json['tables'] = $this->model_repair_defragmentation->getTables($column_name);
        $json['primary'] = $this->model_repair_defragmentation->getPrimary($column_name);
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));

    }

    public function getItems() {
        $this->load->model('repair/defragmentation');
        $column = $this->request->post['column_name'];
        $table = $this->request->post['table_main'];
        
        $results = $this->model_repair_defragmentation->getItems($column, $table);

        $json = array();
        foreach ($results as $key => $result) {
            $json['items'][] = $key;
        }

        $json['total_items'] = count($json['items']);

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    public function defrag() {
        $json = array();

        if(!isset($this->request->post['current_key'])){
            $json['current_key'] =  -1;
        } else {
            $json['current_key'] = $this->request->post['current_key'];
        }

        if(!isset($_SESSION['time'])){
            $_SESSION['time'] = time();
        } 
        
        $tables = array();
        
        $this->load->model('repair/defragmentation');
        
        
        if(!empty($this->request->post['column'])){
            
            $json['current_key'] = $json['current_key'] + 1;
            $json['column'] = $this->request->post['column'];
            $json['item'] = $this->request->post['item'];
            $json['table_main'] = $this->request->post['table_main'];
            $json['total'] = $this->request->post['total'];
            $json['total_items'] = $this->request->post['total_items'];
            
            $json['total'] = $json['total'] + 1; 
        
            $tables = $this->request->post['table_name'];

            $json['query'] = $this->model_repair_defragmentation->defragmentation($json['item'], $json['current_key'], $json['column'], $tables, $json['total_items']);
        } 
        
        
        
        foreach ($tables as $key => $table) {
            $json['tables'][] = $tables[$key]['value'];
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function stop() {
        $json = array();
        
        if(!empty($_SESSION['time'])){
            $time = time() - $_SESSION['time'];
        } else {
            $time = time();
        }
        
        $json['time'] = "Time: <span>" . gmdate("H:i:s", $time) . "</span>";

        unset($_SESSION['time']);

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}
