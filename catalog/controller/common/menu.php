<?php
class ControllerCommonMenu extends Controller {
	public function index() {
		$this->load->language('common/menu');

		// Menu
		$this->load->model('catalog/category');

		$this->load->model('catalog/product');

		$data['categories'] = array();

		$categories = $this->model_catalog_category->getCategories(0);

		foreach ($categories as $category) {
			if ($category['top']) {
				// Level 2
				$children_data = array();

				$children = $this->model_catalog_category->getCategories($category['category_id']);

				foreach ($children as $child) {

                    // Level 3
                    $grandchildren_data = array();

                    $grandchildren = $this->model_catalog_category->getCategories($child['category_id']);

                    foreach ($grandchildren as $grandchild) {

                        $grandchild_filter_data = array(
                            'filter_category_id'  => $grandchild['category_id'],
                            'filter_sub_category' => true
                        );

                        $grandchildren_data[] = array(
                            'name'  => $grandchild['name'] . $this->config->get('config_product_count'),
                            'href'  => $this->url->link('product/category', 'path=' . $category['category_id'] . '_' . $child['category_id'] . '_' . $grandchild['category_id'])
                        );
                    }


					$filter_data = array(
						'filter_category_id'  => $child['category_id'],
						'filter_sub_category' => true
					);

					$children_data[] = array(
						'name'  => $child['name'] . $this->config->get('config_product_count'),
						'href'  => $this->url->link('product/category', 'path=' . $category['category_id'] . '_' . $child['category_id']),
                        'children' => $grandchildren_data,
					);
				}

				// Level 1
				$data['categories'][] = array(
					'name'     => $category['name'],
					'children' => $children_data,
					'column'   => $category['column'] ? $category['column'] : 1,
					'href'     => $this->url->link('product/category', 'path=' . $category['category_id'])
				);
			}
		}


		// if not top

		$data['categories2'] = array();


        foreach ($categories as $category) {
            if ($category['top'] == false) {
                // Level 2
                $children_data = array();

                $children = $this->model_catalog_category->getCategories($category['category_id']);

                foreach ($children as $child) {

                    // Level 3
                    $grandchildren_data = array();

                    $grandchildren = $this->model_catalog_category->getCategories($child['category_id']);

                    foreach ($grandchildren as $grandchild) {

                        $grandchild_filter_data = array(
                            'filter_category_id'  => $grandchild['category_id'],
                            'filter_sub_category' => true
                        );

                        $grandchildren_data[] = array(
                            'name'  => $grandchild['name'] . $this->config->get('config_product_count'),
                            'href'  => $this->url->link('product/category', 'path=' . $category['category_id'] . '_' . $child['category_id'] . '_' . $grandchild['category_id'])
                        );
                    }


                    $filter_data = array(
                        'filter_category_id'  => $child['category_id'],
                        'filter_sub_category' => true
                    );

                    $children_data[] = array(
                        'name'  => $child['name'] . $this->config->get('config_product_count'),
                        'href'  => $this->url->link('product/category', 'path=' . $category['category_id'] . '_' . $child['category_id']),
                        'children' => $grandchildren_data,
                    );
                }

                // Level 1
                $data['categories2'][] = array(
                    'name'     => $category['name'],
                    'children' => $children_data,
                    'column'   => $category['column'] ? $category['column'] : 1,
                    'href'     => $this->url->link('product/category', 'path=' . $category['category_id'])
                );
            }
        }

		return $this->load->view('common/menu', $data);
	}
}
