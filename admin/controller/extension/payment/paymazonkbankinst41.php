<?php
class ControllerExtensionPaymentpaymazonkbankinst41 extends Controller {

	private $error = array();

	public function index() {
		$this->load->language('extension/payment/paymazonkbankinst41');

		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('extension/payment/paymazon');
		$this->load->model('setting/setting');
		$this->load->model('localisation/order_status');
		$this->config->get('curency');


		if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
			$validate_return = $this->validate();
			if (isset($validate_return['result'])) {
				if ($validate_return['result'] === TRUE) {
					$this->settings = array(
						'payment_paymazonkbankinst41_pgcode' => 'kbank-smartpay', // DO NOT CHANGE THIS!
						'payment_paymazonkbankinst41_shopid' => 41, // DO NOT CHANGE THIS!
						'payment_paymazonkbankinst41_new_status' => 1,
						'payment_paymazonkbankinst41_pending_status' => '1',
						'payment_paymazonkbankinst41_complete_status' => '5',
						'payment_paymazonkbankinst41_success_status' => '5',
						'payment_paymazonkbankinst41_canceled_status' => '7',
						'payment_paymazonkbankinst41_failed_status' => '10',
						'payment_paymazonkbankinst41_waiting_status' => '2',
					);
					$this->settings = array_merge($this->settings, $this->request->post);
					
					
					$this->model_setting_setting->editSetting('payment_paymazonkbankinst41', $this->settings);
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
		if (isset($this->error['paymazonkbankinst41_currency_conversion'])) {
			$data['error_currency_conversion'] = $this->error['paymazonkbankinst41_currency_conversion'];
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
			'href' => $this->url->link('extension/payment/paymazonkbankinst41', 'user_token=' . $this->session->data['user_token'], true)
		);
		$data['action'] = $this->url->link('extension/payment/paymazonkbankinst41', 'user_token=' . $this->session->data['user_token'], true);
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'].'&type=payment', true);

		$inputs = array(
			'payment_paymazonkbankinst41_display_name',
			'payment_paymazonkbankinst41_minimum_total',
			'payment_paymazonkbankinst41_environment',
			'payment_paymazonkbankinst41_merchant_id',
			'payment_paymazonkbankinst41_shared_key',
			'payment_paymazonkbankinst41_order_status_id',
			'payment_paymazonkbankinst41_geo_zone_id',
			'payment_paymazonkbankinst41_sort_order',
			'payment_paymazonkbankinst41_3d_secure',
			'payment_paymazonkbankinst41_currency_conversion',
			'payment_paymazonkbankinst41_status',
			'payment_paymazonkbankinst41_expiry_duration',
			'payment_paymazonkbankinst41_expiry_unit',
			'payment_paymazonkbankinst41_custom_field1',
			'payment_paymazonkbankinst41_custom_field2',
			'payment_paymazonkbankinst41_custom_field3',
			'payment_paymazonkbankinst41_installment_shopids',
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
		$data['shopids'] = $this->config->get('payment_paymazonkbankinst41_installment_shopids');
		if (is_array($data['shopids']) && (count($data['shopids']) > 0)) {
			foreach ($data['shopids'] as &$keval) {
				$keval['tenors'] = array();
				if (isset($keval['terms'])) {
					if (is_array($keval['terms']) && count($keval['terms'])) {
						foreach ($keval['terms'] as $val) {
							if (isset($val['value'])) {
								$keval['tenors'][] = $val['value'];
							}
						}
					}
				}
			}
		}
		$data['installment_tenors'] = $this->model_extension_payment_paymazon->getInstallmentTenors();
		
		
		
		
		
		
		
		
		
		
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		
		
		if(!$this->currency->has('THB')) {
			$data['curr'] = true;
		} else {
			$data['curr'] = false;
		}
		
		
		
		$this->response->setOutput($this->load->view('extension/payment/paymazonkbankinst41', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/payment/paymazonkbankinst41')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		// check for empty values
		if (!$this->request->post['payment_paymazonkbankinst41_display_name']) {
			$this->error['display_name'] = $this->language->get('error_display_name');
		}
		// default values
		if (!$this->request->post['payment_paymazonkbankinst41_environment']) {
				$this->request->post['paymazonkbankinst41_environment'] = 'sandbox';
		}
		// check for empty values
		if (!$this->request->post['payment_paymazonkbankinst41_merchant_id']) {
			$this->error['merchant_id'] = $this->language->get('error_merchant_id');
		}
		// check for empty values
		if (!$this->request->post['payment_paymazonkbankinst41_shared_key']) {
			$this->error['shared_key'] = $this->language->get('error_shared_key');
		}
		// check for empty values
		if (!$this->request->post['payment_paymazonkbankinst41_order_status_id']) {
			$this->error['order_status_id'] = $this->language->get('error_order_status_id');
		}
		// currency conversion to THB
		if (!$this->request->post['payment_paymazonkbankinst41_currency_conversion'] && !$this->currency->has('THB')) {
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
            'payment_paymazonkbankinst41_new_status' => 1,
			'payment_paymazonkbankinst41_pending_status' => '1',
			'payment_paymazonkbankinst41_complete_status' => '5',
			'payment_paymazonkbankinst41_success_status' => '5',
			'payment_paymazonkbankinst41_canceled_status' => '7',
			'payment_paymazonkbankinst41_failed_status' => '10',
			'payment_paymazonkbankinst41_waiting_status' => '2',
			'payment_paymazonkbankinst41_pgcode' => 'kbank-smartpay', // DO NOT CHANGE THIS!
			'payment_paymazonkbankinst41_shopid' => 41, // DO NOT CHANGE THIS!
			'payment_paymazonkbankinst41_installment_shopids' => array(array('value' => '41', 'text' => 'CARDHOLDER INTEREST RATE = 0.80% AND MERCHANT FEE  = 2.00%')),
			'payment_paymazonkbankinst41_installment_instmonths' => array(array('value' => '03', 'text' => '03 Month'), array('value' => '06', 'text' => '06 Month')),
        );
        $this->model_setting_setting->editSetting('payment_paymazonkbankinst41', $this->settings);
        $this->model_extension_payment_paymazon->createPaymazonDataTable();
    }
    public function uninstall() {
        $this->load->model('extension/payment/paymazon');
        $this->load->model('setting/setting');

        $this->model_setting_setting->deleteSetting('payment_paymazonkbankinst41');
        $this->model_extension_payment_paymazon->deletePaymazonDataTable();
    }

}




