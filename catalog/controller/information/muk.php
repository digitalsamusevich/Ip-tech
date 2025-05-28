<?php

class ControllerInformationMuk extends Controller {
	private $error = array();

	public function index() {

	    $this->load->model('catalog/information');

	    $payment = $this->model_catalog_information->getInformation(5);
        $curl_handle=curl_init();
        curl_setopt($curl_handle, CURLOPT_URL,'https://api.muk.ua/69bee229b99f1ce4b1232708c76a8236/json/PRICE/');
        curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl_handle, CURLOPT_USERAGENT, 'Your application name');
        $query = curl_exec($curl_handle);
        curl_close($curl_handle);
        $data['products'] = json_decode($query,true);

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

        $this->response->setOutput($this->load->view('information/muk', $data));
	}

}