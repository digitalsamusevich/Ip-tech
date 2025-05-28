<?php

class ControllerInformationWarranty extends Controller {
	private $error = array();

	public function index() {

	    $this->load->model('catalog/information');

	    $warranty = $this->model_catalog_information->getInformation(3);

        $this->load->language('information/warranty');
        $this->document->setTitle($warranty['title']);
        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        );

        $data['breadcrumbs'][] = array(
            'text' => $warranty['title'],
            'href' => $this->url->link('information/warranty')
        );

        $data['description']= html_entity_decode($warranty['description']);
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

        $this->response->setOutput($this->load->view('information/warranty', $data));
	}

}