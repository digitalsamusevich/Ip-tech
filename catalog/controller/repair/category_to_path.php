<?php
class ControllerRepairCategoryToPath extends Controller {
	public function index() {
		
        $this->load->model('repair/category_to_path');

        $category_id = 0;
 
        $this->model_repair_category_to_path->repairCategories($category_id);

	}
}
