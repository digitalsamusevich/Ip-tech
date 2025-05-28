<?php

class ControllerInformationPayment extends Controller {
	private $error = array();

	public function index() {

	    $this->load->model('catalog/information');

	    $payment = $this->model_catalog_information->getInformation(5);

        $this->load->language('information/payment');
        $this->document->setTitle($payment['title']);
        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        );

        $data['breadcrumbs'][] = array(
            'text' => $payment['title'],
            'href' => $this->url->link('information/payment')
        );

        $data['description']= html_entity_decode($payment['description']);
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

        $this->response->setOutput($this->load->view('information/payment', $data));
	}

}