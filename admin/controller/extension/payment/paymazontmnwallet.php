<?php
class ControllerExtensionPaymentpaymazontmnwallet extends Controller {

	private $error = array();

	public function index() {
		$this->load->language('extension/payment/paymazontmnwallet');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');
		$this->load->model('localisation/order_status');
		$this->config->get('curency');


		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
			$validate_return = $this->validate();
			if (isset($validate_return['result'])) {
				if ($validate_return['result'] === TRUE) {
					$this->settings = array(
						'payment_paymazontmnwallet_new_status' => 1,
						'payment_paymazontmnwallet_pending_status' => '1',
						'payment_paymazontmnwallet_complete_status' => '5',
						'payment_paymazontmnwallet_success_status' => '5',
						'payment_paymazontmnwallet_canceled_status' => '7',
						'payment_paymazontmnwallet_failed_status' => '10',
						'payment_paymazontmnwallet_waiting_status' => '2',
						'payment_paymazontmnwallet_pgcode' => 'tmn-wallet', // DO NOT CHANGE THIS!
					);
					$this->settings = array_merge($this->settings, $this->request->post);
					$this->model_setting_setting->editSetting('payment_paymazontmnwallet', $this->settings);
					$this->session->data['success'] = $this->language->get('text_success');
					$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
				}
			}
		}
		
		
		
		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}
		if (isset($this->error['display_name'])) {
			$data['error_display_name'] = $this->error['display_name'];
		} else {
			$data['error_display_name'] = false;
		}
		if (isset($this->error['merchant_id'])) {
			$data['error_merchant_id'] = $this->error['merchant_id'];
		} else {
			$data['error_merchant_id'] = false;
		}
		if (isset($this->error['shared_key'])) {
			$data['error_shared_key'] = $this->error['shared_key'];
		} else {
			$data['error_shared_key'] = false;
		}
		if (isset($this->error['paymazontmnwallet_currency_conversion'])) {
			$data['error_currency_conversion'] = $this->error['paymazontmnwallet_currency_conversion'];
		} else {
			$data['error_currency_conversion'] = false;
		}
		$data['breadcrumbs'] = array();
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true)
		);
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/payment/paymazontmnwallet', 'user_token=' . $this->session->data['user_token'], true)
		);
		$data['action'] = $this->url->link('extension/payment/paymazontmnwallet', 'user_token=' . $this->session->data['user_token'], true);
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'].'&type=payment', true);

		$inputs = array(
			'payment_paymazontmnwallet_display_name',
			'payment_paymazontmnwallet_minimum_total',
			'payment_paymazontmnwallet_environment',
			'payment_paymazontmnwallet_merchant_id',
			'payment_paymazontmnwallet_shared_key',
			'payment_paymazontmnwallet_order_status_id',
			'payment_paymazontmnwallet_geo_zone_id',
			'payment_paymazontmnwallet_sort_order',
			'payment_paymazontmnwallet_3d_secure',
			'payment_paymazontmnwallet_currency_conversion',
			'payment_paymazontmnwallet_status',
			'payment_paymazontmnwallet_expiry_duration',
			'payment_paymazontmnwallet_expiry_unit',
			'payment_paymazontmnwallet_custom_field1',
			'payment_paymazontmnwallet_custom_field2',
			'payment_paymazontmnwallet_custom_field3',
		);
		foreach ($inputs as $input) {
			if (isset($this->request->post[$input])) {
				$data[$input] = $this->request->post[$input];
			} else {
				$data[$input] = $this->config->get($input);
			}
		}
		$this->load->model('localisation/order_status');
		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		$this->load->model('localisation/geo_zone');
		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
		$data['expiry'] = array();
		$unit1 = array('id' => 1,'unit' => "minutes");
		$unit2 = array('id' => 2,'unit' => "hours");
		$unit3 = array('id' => 3,'unit' => "days");
		array_push($data['expiry'] , $unit1, $unit2, $unit3);
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		
		
		if(!$this->currency->has('THB')) {
			$data['curr'] = true;
		} else {
			$data['curr'] = false;
		}
		$this->response->setOutput($this->load->view('extension/payment/paymazontmnwallet', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/payment/paymazontmnwallet')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		// check for empty values
		if (!$this->request->post['payment_paymazontmnwallet_display_name']) {
			$this->error['display_name'] = $this->language->get('error_display_name');
		}
		// default values
		if (!$this->request->post['payment_paymazontmnwallet_environment']) {
				$this->request->post['paymazontmnwallet_environment'] = 'sandbox';
		}
		// check for empty values
		if (!$this->request->post['payment_paymazontmnwallet_merchant_id']) {
			$this->error['merchant_id'] = $this->language->get('error_merchant_id');
		}
		// check for empty values
		if (!$this->request->post['payment_paymazontmnwallet_shared_key']) {
			$this->error['shared_key'] = $this->language->get('error_shared_key');
		}
		// check for empty values
		if (!$this->request->post['payment_paymazontmnwallet_order_status_id']) {
			$this->error['order_status_id'] = $this->language->get('error_order_status_id');
		}
		// currency conversion to THB
		if (!$this->request->post['payment_paymazontmnwallet_currency_conversion'] && !$this->currency->has('THB')) {
			$this->error['currency_conversion'] = $this->language->get('error_currency_conversion');
		}
		
		if ($this->error) {
			return array(
				'result'				=> false,
				'error'					=> $this->error,
			);
		} else {
			return array(
				'result'				=> true,
				'error'					=> false,
			);
		}
	}
	
	
	
	//======================================
	public function install() {
        $this->load->model('extension/payment/paymazon');
        $this->load->model('setting/setting');

        $this->settings = array(
            'payment_paymazontmnwallet_new_status' => 1,
			'payment_paymazontmnwallet_pending_status' => '1',
			'payment_paymazontmnwallet_complete_status' => '5',
			'payment_paymazontmnwallet_success_status' => '5',
			'payment_paymazontmnwallet_canceled_status' => '7',
			'payment_paymazontmnwallet_failed_status' => '10',
			'payment_paymazontmnwallet_waiting_status' => '2',
			'payment_paymazontmnwallet_pgcode' => 'tmn-wallet', // DO NOT CHANGE THIS!
        );
        $this->model_setting_setting->editSetting('payment_paymazontmnwallet', $this->settings);
        $this->model_extension_payment_paymazon->createPaymazonDataTable();
    }
    public function uninstall() {
        $this->load->model('extension/payment/paymazon');
        $this->load->model('setting/setting');

        $this->model_setting_setting->deleteSetting('payment_paymazontmnwallet');
        $this->model_extension_payment_paymazon->deletePaymazonDataTable();
    }

}




