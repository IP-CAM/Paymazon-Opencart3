<?php
class ControllerExtensionPaymentPaymazonkbank extends Controller {

	private $error = array();

	public function index() {
		$this->load->language('extension/payment/paymazonkbank');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');
		$this->load->model('localisation/order_status');
		$this->config->get('curency');


		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
			$validate_return = $this->validate();
			if (isset($validate_return['result'])) {
				if ($validate_return['result'] === TRUE) {
					$this->settings = array(
						'payment_paymazonkbank_new_status' => 1,
						'payment_paymazonkbank_pending_status' => '1',
						'payment_paymazonkbank_complete_status' => '5',
						'payment_paymazonkbank_success_status' => '5',
						'payment_paymazonkbank_canceled_status' => '7',
						'payment_paymazonkbank_failed_status' => '10',
						'payment_paymazonkbank_waiting_status' => '2',
						'payment_paymazonkbank_pgcode' => 'kbank-pgpayment', // DO NOT CHANGE THIS!
					);
					$this->settings = array_merge($this->settings, $this->request->post);
					$this->model_setting_setting->editSetting('payment_paymazonkbank', $this->settings);
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
		if (isset($this->error['paymazonkbank_currency_conversion'])) {
			$data['error_currency_conversion'] = $this->error['paymazonkbank_currency_conversion'];
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
			'href' => $this->url->link('extension/payment/paymazonkbank', 'user_token=' . $this->session->data['user_token'], true)
		);
		$data['action'] = $this->url->link('extension/payment/paymazonkbank', 'user_token=' . $this->session->data['user_token'], true);
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'].'&type=payment', true);

		$inputs = array(
			'payment_paymazonkbank_display_name',
			'payment_paymazonkbank_minimum_total',
			'payment_paymazonkbank_environment',
			'payment_paymazonkbank_merchant_id',
			'payment_paymazonkbank_shared_key',
			'payment_paymazonkbank_order_status_id',
			'payment_paymazonkbank_geo_zone_id',
			'payment_paymazonkbank_sort_order',
			'payment_paymazonkbank_3d_secure',
			'payment_paymazonkbank_currency_conversion',
			'payment_paymazonkbank_status',
			'payment_paymazonkbank_expiry_duration',
			'payment_paymazonkbank_expiry_unit',
			'payment_paymazonkbank_custom_field1',
			'payment_paymazonkbank_custom_field2',
			'payment_paymazonkbank_custom_field3',
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
		$this->response->setOutput($this->load->view('extension/payment/paymazonkbank', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/payment/paymazonkbank')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		// check for empty values
		if (!$this->request->post['payment_paymazonkbank_display_name']) {
			$this->error['display_name'] = $this->language->get('error_display_name');
		}
		// default values
		if (!$this->request->post['payment_paymazonkbank_environment']) {
				$this->request->post['paymazonkbank_environment'] = 'sandbox';
		}
		// check for empty values
		if (!$this->request->post['payment_paymazonkbank_merchant_id']) {
			$this->error['merchant_id'] = $this->language->get('error_merchant_id');
		}
		// check for empty values
		if (!$this->request->post['payment_paymazonkbank_shared_key']) {
			$this->error['shared_key'] = $this->language->get('error_shared_key');
		}
		// check for empty values
		if (!$this->request->post['payment_paymazonkbank_order_status_id']) {
			$this->error['order_status_id'] = $this->language->get('error_order_status_id');
		}
		// currency conversion to THB
		if (!$this->request->post['payment_paymazonkbank_currency_conversion'] && !$this->currency->has('THB')) {
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
            'payment_paymazonkbank_new_status' => 1,
			'payment_paymazonkbank_pending_status' => '1',
			'payment_paymazonkbank_complete_status' => '5',
			'payment_paymazonkbank_success_status' => '5',
			'payment_paymazonkbank_canceled_status' => '7',
			'payment_paymazonkbank_failed_status' => '10',
			'payment_paymazonkbank_waiting_status' => '2',
			'payment_paymazonkbank_pgcode' => 'kbank-pgpayment', // DO NOT CHANGE THIS!
        );
        $this->model_setting_setting->editSetting('payment_paymazonkbank', $this->settings);
        $this->model_extension_payment_paymazon->createPaymazonDataTable();
    }
    public function uninstall() {
        $this->load->model('extension/payment/paymazon');
        $this->load->model('setting/setting');

        $this->model_setting_setting->deleteSetting('payment_paymazonkbank');
        $this->model_extension_payment_paymazon->deletePaymazonDataTable();
    }

}




