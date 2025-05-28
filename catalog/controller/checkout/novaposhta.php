<?php
require DIR_SYSTEM . 'library/nova-poshta-api-2/src/Delivery/NovaPoshtaApi2.php';

class ControllerCheckoutNovaPoshta extends Controller {

    public function index() {
		// cities

        if (isset($this->request->get['filter_city'])) {
            $search = $this->request->get['filter_city'];
        } else {
            $search = '1';
        }
        $arr = [];
        $i = 0;

        $np = new \LisDev\Delivery\NovaPoshtaApi2('140794b3c824d9454f012284ce2b2901');
        $getCities= $np->getCities();
        $cities = $getCities['data'];
        forEach($cities as $city) {
            $pos_city = mb_stripos($city['Description'],'(');
            if($pos_city !== false) {
                $str = mb_substr($city['Description'],0,$pos_city);
                $pos = mb_stripos($str, $search);
            } else {
                $pos = mb_stripos($city['Description'], $search);
             }

            if($pos !== false && $i<7) {
                $i++;
                array_push($arr,$city);
            }
        }

        $this->response->setOutput(json_encode($arr));
    }

    public function department() {
        $search = $this->request->get['filter_department'];
        $city = $this->request->get['city'];
        $arr = [];
        $i = 0;
        $np = new \LisDev\Delivery\NovaPoshtaApi2('140794b3c824d9454f012284ce2b2901');
        $getDepartments = $np->getWarehouses($city);
        $departments = $getDepartments['data'];

        forEach($departments as $department) {
            $pos_city = mb_stripos($department['Description'],'№');
            if($pos_city !== false) {
                $str = mb_substr($department['Description'],0,$pos_city+4);
                $pos = mb_stripos($str, $search);
            } else {
                $pos = mb_stripos($department['Description'], $search);
             }

            if($pos !== false && $i<7) {
                $i++;
                array_push($arr,$department);
            }
        }

        $this->response->setOutput(json_encode($arr));
    }
}