<?php
class ControllerExtensionModuleBenefits extends Controller {
	public function index($setting) {
		if (isset($setting['module_description'][$this->config->get('config_language_id')])) {
			$data['heading_title'] = html_entity_decode($setting['module_description'][$this->config->get('config_language_id')]['title'], ENT_QUOTES, 'UTF-8');
			$data['benefits'] = html_entity_decode($setting['module_description'][$this->config->get('config_language_id')]['description'], ENT_QUOTES, 'UTF-8');
			
			if (isset($setting['icon']) && is_file(DIR_IMAGE . $setting['icon'])) {
				$data['icon_thumb'] = $this->model_tool_image->resize($setting['icon'], 32, 32);
			} else {
				$data['icon_thumb'] = $this->model_tool_image->resize('no_image.png', 32, 32);
			}
			return $this->load->view('extension/module/benefits', $data);
		}
	}
}