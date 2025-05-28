<?php

class ControllerInformationPhone extends Controller {
    private $error = array();

    public function index() {
        // Завантажуємо модель для отримання інформації
        $this->load->model('catalog/information');

        // Отримуємо інформацію про телефон (ID 7 для прикладу)
        $phone = $this->model_catalog_information->getInformation(7);

        // Завантажуємо мовний файл
        $this->load->language('information/phone');
        
        // Встановлюємо заголовок сторінки
        $this->document->setTitle($phone['title']);

        // Додаємо хлібні крихти
        $data['breadcrumbs'] = array();
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        );

        $data['breadcrumbs'][] = array(
            'text' => $phone['title'],
            'href' => $this->url->link('information/phone')
        );

        // Опис сторінки
        $data['description'] = html_entity_decode($phone['description']);

        // Завантажуємо інші компоненти сторінки
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        // Виводимо шаблон
        $this->response->setOutput($this->load->view('information/phone', $data));
    }
}
