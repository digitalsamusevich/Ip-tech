<?php

class ControllerInformationContact extends Controller {
	private $error = array();

	public function index() {

	    // Завантаження моделі для отримання інформації
	    $this->load->model('catalog/information');

	    // Отримуємо інформацію по ID, наприклад ID 7 для сторінки контактів
	    $contact = $this->model_catalog_information->getInformation(7);

        // Завантажуємо мовні файли
        $this->load->language('information/contact');
        $this->document->setTitle($contact['title']); // Встановлюємо заголовок сторінки

        // Додаємо хлібні крихти
        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        );

        $data['breadcrumbs'][] = array(
            'text' => $contact['title'],
            'href' => $this->url->link('information/contact')
        );

        // Опис сторінки
        $data['description'] = html_entity_decode($contact['description']); // Розкодування HTML-entities

        // Завантажуємо інші компоненти сторінки
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

        // Виводимо сторінку за допомогою відповідного шаблону
        $this->response->setOutput($this->load->view('information/contact', $data));
	}

}
