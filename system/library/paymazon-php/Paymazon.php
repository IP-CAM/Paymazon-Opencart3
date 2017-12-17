<?php
include_once(__DIR__ . '/src/Paymazon_Curl.php');
include_once(__DIR__ . '/src/Paymazon_Utils.php');

Class Paymazon_Config {
	public static $CONFIG = array();
	public static function setMerchantID($merchant_id) {
		self::$CONFIG['merchant_id'] = $merchant_id;
	}
	public static function setSharedKey($shared_key) {
		self::$CONFIG['shared_key'] = $shared_key;
	}
	public static function setEnvironment($env) {
		self::$CONFIG['mode'] = $env;
	}
	public static function setPaymentGateway($pg_code) {
		self::$CONFIG['pg_code'] = $pg_code;
	}
}


Class Paymazon {
	protected $endpoint;
	protected $config;
	protected $pg_code;
	
	protected $Paymazon_Curl;
	protected $Paymazon_Utils;
	function __construct($context = array()) {
		$this->endpoint = (isset($context['mode']) ? $this->set_endpoint($context['mode']) : $this->set_endpoint('sandbox'));
		$this->config = array(
			'merchant_id'			=> (isset($context['merchant_id']) ? $context['merchant_id'] : ''),
			'shared_key'			=> (isset($context['shared_key']) ? $context['shared_key'] : ''),
		);
		$this->pg_code = (isset($context['pg_code']) ? $context['pg_code'] : '');
		$this->pg_code = (is_string($this->pg_code) ? strtolower($this->pg_code) : '');
		
		//--------------------------------------------
		// Libraries Object
		$this->Paymazon_Curl = new Paymazon_Curl();
		$this->Paymazon_Utils = new Paymazon_Utils();
		//----------------------------------------------
	}
	function set_endpoint($server_mode = null) {
		if (!isset($server_mode)) {
			$server_mode = 'sandbox';
		}
		$server_mode = (is_string($server_mode) ? strtolower($server_mode) : 'sandbox');
		$url = "";
		switch ($server_mode) {
			case 'live':
			case 'production':
				$url = 'https://payment.paymazon.com/version/v1/myarena/';
			break;
			case 'development':
			case 'sandbox':
			case 'test':
			default:
				$url = 'https://payment.nababan.net/version/v1/myarena/';
			break;
		}
		return filter_var($url, FILTER_VALIDATE_URL);
	}
	//=============================================
	function set_payment_structure($input_params) {
		$this->payment_structure = $input_params;
		return $this;
	}
	function get_payment_structure() {
		return $this->payment_structure;
	}
	//=============================================
	public function create_payment_structure($method, $pg_code, $input_params = array(), $cutom_params = array(), $order_products = array()) {
		$PaymentStructure = array();
		$method = (is_string($method) ? strtolower($method) : 'create');
		switch (strtolower($method)) {
			case 'create':
				$Queryusers = array(
					'account' => (isset($input_params['customer_id']) ? $input_params['customer_id'] : ''),
					'email' => (isset($input_params['email']) ? $input_params['email'] : ''),
					'billing_address'	=> array(
						'forename'			=> (isset($input_params['payment_firstname']) ? $input_params['payment_firstname'] : ''),
						'surname'			=> (isset($input_params['payment_lastname']) ? $input_params['payment_lastname'] : ''),
						'email'				=> (isset($input_params['payment_email']) ? $input_params['payment_email'] : (isset($input_params['email']) ? $input_params['email'] : '')),
						'phone'				=> (isset($input_params['telephone']) ? $input_params['telephone'] : ''),
						'line1'				=> (isset($input_params['payment_address_1']) ? $input_params['payment_address_1'] : ''),
						'line2'				=> (isset($input_params['payment_address_2']) ? $input_params['payment_address_2'] : ''),
						'city'				=> (isset($input_params['payment_city']) ? $input_params['payment_city'] : ''),
						'province'			=> (isset($input_params['payment_zone']) ? $input_params['payment_zone'] : ''),
						'country'			=> (isset($input_params['payment_country']) ? $input_params['payment_country'] : ''),
						'zipcode'			=> (isset($input_params['payment_postcode']) ? $input_params['payment_postcode'] : ''),
					),
					'shipping_address'	=> array(
						'forename'			=> (isset($input_params['shipping_firstname']) ? $input_params['shipping_firstname'] : ''),
						'surname'			=> (isset($input_params['shipping_lastname']) ? $input_params['shipping_lastname'] : ''),
						'email'				=> (isset($input_params['email']) ? $input_params['email'] : ''),
						'phone'				=> (isset($input_params['telephone']) ? $input_params['telephone'] : ''),
						'line1'				=> (isset($input_params['shipping_address_1']) ? $input_params['shipping_address_1'] : ''),
						'line2'				=> (isset($input_params['shipping_address_2']) ? $input_params['shipping_address_2'] : ''),
						'city'				=> (isset($input_params['shipping_city']) ? $input_params['shipping_city'] : ''),
						'province'			=> (isset($input_params['shipping_zone']) ? $input_params['shipping_zone'] : ''),
						'country'			=> (isset($input_params['shipping_country']) ? $input_params['shipping_country'] : ''),
						'zipcode'			=> (isset($input_params['shipping_postcode']) ? $input_params['shipping_postcode'] : ''),
					),
				);
				// Merchants
				$Querymerchants = array(
					'service'					=> 'myarena',
					'merchant_id'				=> (isset($this->config['merchant_id']) ? sprintf('%s', $this->config['merchant_id']) : ''),
				);
				// ### Get User Consent URL
				// Redirect
				$Queryredirects = array(
					'notify_url'				=> (isset($cutom_params['url']['notify_url']) ? $cutom_params['url']['notify_url'] : ''),
					'success_url'				=> (isset($cutom_params['url']['success_url']) ? $cutom_params['url']['success_url'] : ''),
					'cancel_url'				=> (isset($cutom_params['url']['cancel_url']) ? $cutom_params['url']['cancel_url'] : ''),
					'failed_url'				=> (isset($cutom_params['url']['failed_url']) ? $cutom_params['url']['failed_url'] : ''),
				);
				// Payments
				$Querypayments = array(
					'request_id'				=> (isset($input_params['request_id']) ? $input_params['request_id'] : ''), //Create unique request_id
					'currency'					=> (isset($input_params['currency_code']) ? $input_params['currency_code'] : ''),
					'amount'					=> 0,
					'items'						=> array(),
				);
				// Items
				if (is_array($order_products) && (count($order_products) > 0)) {
					foreach ($order_products as $keval) {
						$item_details = array(
							'item_id'		=> (isset($keval['cart_id']) ? $keval['cart_id'] : 1),
							'service' 		=> (isset($keval['model']) ? $keval['model'] : ''),
							'item_name' 	=> (isset($keval['name']) ? $keval['name'] : ''),
							'item_details'	=> (isset($keval['name']) ? $keval['name'] : ''),
							'details' 		=> (isset($keval['name']) ? $keval['name'] : ''),
							'product_id' 	=> (isset($keval['product_id']) ? $keval['product_id'] : date('YmdHis')),
							'price' 		=> (isset($keval['total_with_tax']) ? $keval['total_with_tax'] : 0),
							'reference' 	=> array(
								'ref1'			=> (isset($cutom_params['custom']['ref1']) ? $cutom_params['custom']['ref1'] : ''),
								'ref2'			=> (isset($cutom_params['custom']['ref2']) ? $cutom_params['custom']['ref2'] : ''),
								'ref3'			=> (isset($cutom_params['custom']['ref3']) ? $cutom_params['custom']['ref3'] : ''),
							),
						);
						array_push($Querypayments['items'], $item_details);
					}
				}
				//Get Authorization URL returns the redirect URL that could be used to get user's consent
				$Queryparams = array(
					'request_id' 	=> $Querypayments['request_id'], /// Create unique request_id
					'currency'		=> $Querypayments['currency'],
					'return_url' 	=> $Queryredirects['success_url'],
					'cancel_url' 	=> $Queryredirects['cancel_url'],
				);
				# Temporary for fees items, next from $logsync['item_lists']
				###########################################################
				$Queryparams['items'] = $Querypayments['items'];
				// Items to version-mode
				if (count($Querypayments['items']) > 0) {
					$for_i = 1;
					foreach ($Querypayments['items'] as &$keval) {
						$keval['item_name'] = $keval['service'];
						unset($keval['service']);
						$keval['item_price'] = sprintf('%.2f', $keval['price']);
						unset($keval['price']);
						$keval['item_id'] = $for_i;
						$keval['item_unit'] = 1;
						//----------------------
						$Querypayments['amount'] += ((int)$keval['item_price'] * (int)$keval['item_unit']);
						$Querypayments['amount'] = sprintf('%.2f', $Querypayments['amount']);
						$for_i += 1;
					}
				}
				# Make Encrypt
				//============
				ksort($Querypayments);
				$encrypt_fields = array();
				foreach ($Querypayments as $ke => $val) {
					$encrypt_fields[$ke] = $val;
				}
				unset($encrypt_fields['items']);
				$Querypayments['encrypt_string'] = implode('|', $encrypt_fields);
				$Querypayments['encrypt'] = base64_encode(hash_hmac('sha256', $Querypayments['encrypt_string'], (isset($this->config['shared_key']) ? sprintf('%s', $this->config['shared_key']) : ''), TRUE));
				
				//-----------------
				$PaymentStructure['merchant'] = $Querymerchants;
				$PaymentStructure['redirect'] = $Queryredirects;
				$PaymentStructure['payment'] = $Querypayments;
				$PaymentStructure['user'] = $Queryusers;
			break;
		}
		
		
		
		
		
		
		
		
		
		
		
		
		$this->set_payment_structure($PaymentStructure);
	}
	
	
	//---------------------------------------------
	function get_payment_result_by_curl($method, $uri_template) {
		$url = $this->endpoint;
		$url .= (isset($this->pg_code) ? $this->pg_code : '');
		$url .= (is_string($uri_template) ? $uri_template : '');
		$this->Paymazon_Curl->reset_headers();
		$this->Paymazon_Curl->set_headers();
		$this->Paymazon_Curl->add_headers('Content-Type', 'application/json;charset=utf-8');
		if (isset(Paymazon_Config::$CONFIG['merchant_id']) && isset(Paymazon_Config::$CONFIG['shared_key'])) {
			$this->Paymazon_Curl->add_headers('Authorization', "Basic " . base64_encode(Paymazon_Config::$CONFIG['merchant_id'] . ':' . Paymazon_Config::$CONFIG['shared_key']));
		}
		$headers = $this->Paymazon_Curl->create_curl_headers($this->Paymazon_Curl->headers);
		try {
			$create_curl = $this->Paymazon_Curl->create_curl_request($method, $url, $this->Paymazon_Curl->UA, $headers, NULL);
		} catch (Exception $ex) {
			$create_curl = null;
			throw $ex;
		}
		return $create_curl;
	}
	function create_payment_request_by_curl($method, $uri_template) {
		$url = $this->endpoint;
		$url .= (isset($this->pg_code) ? $this->pg_code : '');
		$url .= (is_string($uri_template) ? $uri_template : '');
		$this->Paymazon_Curl->reset_headers();
		$this->Paymazon_Curl->set_headers();
		$this->Paymazon_Curl->add_headers('Content-Type', 'application/x-www-form-urlencoded');
		$headers = $this->Paymazon_Curl->create_curl_headers($this->Paymazon_Curl->headers);
		try {
			$create_curl = $this->Paymazon_Curl->create_curl_request($method, $url, $this->Paymazon_Curl->UA, $headers, $this->payment_structure);
		} catch (Exception $ex) {
			$create_curl = null;
			throw $ex;
		}
		return $create_curl;
	}
	function create_new_request_id($timezone = 'Asia/Bangkok') {
		$microtime = microtime(true);
		$micro = sprintf("%06d",($microtime - floor($microtime)) * 1000000);
		$DateObject = new DateTime(date("Y-m-d H:i:s.{$micro}", $microtime));
		$DateObject->setTimezone(new DateTimeZone($timezone));
		return $DateObject->format('YmdHisu');
	}	
	//-----------------------------------------------------------------------------
	// Utilities
	//-----------------------------------------------------------------------------
	// Access Protected instance
	function get_php_input_request() {
		return $this->Paymazon_Utils->php_input_request();
	}
	
	
	// Create Internal Encrypt
	function get_encrypt_string($method, $input_params) {
		$method = (is_string($method) ? strtolower($method) : 'identify');
		$encrypt_params = array();
		switch ($method) {
			case 'notify':
				$query_params = array(
					'request_id'					=> (isset($input_params['request_id']) ? $input_params['request_id'] : ''),
					'payment_id'					=> (isset($input_params['payment_id']) ? $input_params['payment_id'] : ''),
					'payment_type'					=> (isset($input_params['payment_type']) ? $input_params['payment_type'] : ''),
					'payment_method'				=> (isset($input_params['payment_method']) ? $input_params['payment_method'] : ''),
				);
				$encrypt_params['string'] = implode("", $query_params);
				if (isset(Paymazon_Config::$CONFIG['shared_key'])) {
					$encrypt_params['hash'] = base64_encode(hash_hmac('sha256', $encrypt_params['string'], Paymazon_Config::$CONFIG['shared_key'], true));
				}
			break;
			case 'identify':
			default:
				$query_params = array(
					'request_id'					=> (isset($input_params['request_id']) ? $input_params['request_id'] : ''),
					'payment_id'					=> (isset($input_params['payment_id']) ? $input_params['payment_id'] : ''),
					'payment_type'					=> (isset($input_params['payment_type']) ? $input_params['payment_type'] : ''),
					'payment_method'				=> (isset($input_params['payment_method']) ? $input_params['payment_method'] : ''),
				);
				$encrypt_params['string'] = implode("", $query_params);
				if (isset(Paymazon_Config::$CONFIG['shared_key'])) {
					$encrypt_params['hash'] = base64_encode(hash_hmac('sha256', $encrypt_params['string'], Paymazon_Config::$CONFIG['shared_key'], true));
				}
			break;
		}
		return $encrypt_params;
	}
	
	
	
	
}
































