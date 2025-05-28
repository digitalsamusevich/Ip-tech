<?php

class ControllerInformationSupport extends Controller {
	private $error = array();

	public function index() {

	    $this->load->model('catalog/information');

	    $support = $this->model_catalog_information->getInformation(8);

        $this->load->language('information/support');
        $this->document->setTitle($support['title']);
        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        );

        $data['breadcrumbs'][] = array(
            'text' => $support['title'],
            'href' => $this->url->link('information/support')
        );

        $data['description']= html_entity_decode($support['description']);
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

        $this->response->setOutput($this->load->view('information/support', $data));
	}

}