<?php

class ControllerInformationDelivery extends Controller {
	private $error = array();

	public function index() {

	    $this->load->model('catalog/information');

	    $delivery = $this->model_catalog_information->getInformation(6);

        $this->load->language('information/delivery');
        $this->document->setTitle($delivery['title']);
        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        );

        $data['breadcrumbs'][] = array(
            'text' => $delivery['title'],
            'href' => $this->url->link('information/delivery')
        );

        $data['description']= html_entity_decode($delivery['description']);
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');
        $this->response->setOutput($this->load->view('information/delivery', $data));
	}

}