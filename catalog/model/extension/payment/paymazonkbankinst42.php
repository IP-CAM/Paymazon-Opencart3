<?php
/*
* Paymazon Payment Modules
*
* https://payment.paymazon.com
*/

class ModelExtensionPaymentpaymazonkbankinst42 extends Model {
    public function getMethod($address, $total) {
        $this->load->language('extension/payment/paymazon');
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('payment_paymazonkbankinst42_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");
        if ($this->config->get('payment_paymazonkbankinst42_minimum_total') > 0 && $this->config->get('payment_paymazonkbankinst42_minimum_total') > $total) {
            $status = false;
        } elseif (!$this->config->get('payment_paymazonkbankinst42_geo_zone_id')) {
            $status = true;
        } elseif ($query->num_rows) {
            $status = true;
        } else {
            $status = false;
        }
        $method_data = array();
        if ($status) {
            $method_data = array(
                'code'				=> 'paymazonkbankinst42',
                'title'				=> $this->config->get('payment_paymazonkbankinst42_display_name'),
                'terms'				=> '',
                'sort_order'		=> $this->config->get('payment_paymazonkbankinst42_sort_order')
            );
        }
        return $method_data;
    }
	//=========================================================
	public function get_order_data_by($by_key, $by_value, $input_params = array()) {
		$by_key = (is_string($by_key) ? strtolower($by_key) : 'seq');
		$by_value = ((is_string($by_value) || is_numeric($by_value)) ? sprintf('%s', $by_value) : '0');
		$sql = sprintf("SELECT * FROM %s WHERE", DB_PREFIX . 'payment_paymazon');
		$query_wheres = array();
		if (isset($input_params['paymazon_payment_code'])) {
			$query_wheres['paymazon_payment_code'] = $input_params['paymazon_payment_code'];
		}
		if (isset($input_params['paymazon_payment_shopid'])) {
			$query_wheres['paymazon_payment_shopid'] = $input_params['paymazon_payment_shopid'];
		}
		if (isset($input_params['paymazon_payment_id'])) {
			$query_wheres['paymazon_payment_id'] = $input_params['paymazon_payment_id'];
		}
		if (isset($input_params['paymazon_payment_status'])) {
			$query_wheres['paymazon_payment_status'] = $input_params['paymazon_payment_status'];
		}
		switch (strtolower($by_key)) {
			case 'seq':
				$sql .= sprintf(" seq = '%d'", $this->db->escape($by_value));
			break;
			case 'payment':
				if (count($query_wheres) > 0) {
					$sql .= "(";
					$for_i = 0;
					foreach ($query_wheres as $key => $val) {
						if ($for_i === 0) {
							$sql .= sprintf(" %s = '%s'", $this->db->escape($key), $this->db->escape($val));
						} else {
							$sql .= sprintf(" AND %s = '%s'", $this->db->escape($key), $this->db->escape($val));
						}
						$for_i += 1;
					}
					$sql .= ")";
				}
			break;
			case 'pgcode':
				$sql .= sprintf(" LOWER(paymazon_payment_code) = LOWER('%s')", $this->db->escape($by_value));
			break;
		}
		$sql .= " LIMIT 1";
		$result = $this->db->query($sql)->row;
		if (!$result) {
			return null;
		}
		return $result;
	}
	public function set_order_data_by($by_key, $by_value, $input_params = array()) {
		$by_key = (is_string($by_key) ? strtolower($by_key) : 'seq');
		$by_value = ((is_string($by_value) || is_numeric($by_value)) ? sprintf('%s', $by_value) : '0');
		$sql = sprintf("UPDATE %s SET", DB_PREFIX . 'payment_paymazon');
		$query_params = array();
		if (isset($input_params['paymazon_payment_status'])) {
			$query_params['paymazon_payment_status'] = $input_params['paymazon_payment_status'];
		}
		$for_i = 0;
		if (count($query_params) > 0) {
			foreach ($query_params as $key => $val) {
				if ($for_i === 0) {
					$sql .= sprintf(" %s = '%s'", $this->db->escape($key), $this->db->escape($val));
				} else {
					$sql .= sprintf(", %s = '%s'", $this->db->escape($key), $this->db->escape($val));
				}
				$for_i += 1;
			}
		}
		if ($for_i > 0) {
			$sql .= ", paymazon_datetime_update = NOW()";
		}
		$sql .= " WHERE";
		switch (strtolower($by_key)) {
			case 'seq':
				$sql .= sprintf(" seq = '%d'", $this->db->escape($by_value));
			break;
			case 'pgcode':
				$sql .= sprintf(" LOWER(paymazon_payment_code) = LOWER('%s')", $this->db->escape($by_value));
			break;
		}
		
		$sql .= " LIMIT 1";
		$result = $this->db->query($sql);
		if (!$result) {
			return null;
		}
		return $result;
	}
	//--------------------------------------------------------
	public function insertNewPaymazonTransaction($order_id, $request_id, $input_params = array()) {
		$order_id = ((is_string($order_id) || is_numeric($order_id)) ? sprintf('%s', $order_id) : '');
		$request_id = (is_string($request_id) ? sprintf('%s', $request_id) : '');
		$query_params = array(
			'paymazon_payment_code'			=> (isset($input_params['paymazon_payment_code']) ? $input_params['paymazon_payment_code'] : ''),
			'paymazon_payment_shopid'		=> (isset($input_params['paymazon_payment_shopid']) ? $input_params['paymazon_payment_shopid'] : '42'),
			'paymazon_payment_id'			=> (isset($input_params['paymazon_payment_id']) ? $input_params['paymazon_payment_id'] : ''),
			'paymazon_payment_status'		=> (isset($input_params['paymazon_payment_status']) ? $input_params['paymazon_payment_status'] : ''),
		);
		$query_params['paymazon_payment_code'] = (is_string($query_params['paymazon_payment_code']) || is_numeric($query_params['paymazon_payment_code'])) ? sprintf("%s", $query_params['paymazon_payment_code']) : '';
		$query_params['paymazon_payment_shopid'] = (is_string($query_params['paymazon_payment_shopid']) || is_numeric($query_params['paymazon_payment_shopid'])) ? sprintf("%d", $query_params['paymazon_payment_shopid']) : '42';
		$query_params['paymazon_payment_id'] = (is_string($query_params['paymazon_payment_id']) || is_numeric($query_params['paymazon_payment_id'])) ? sprintf("%s", $query_params['paymazon_payment_id']) : '';
		$query_params['paymazon_payment_status'] = (is_string($query_params['paymazon_payment_status']) || is_numeric($query_params['paymazon_payment_status'])) ? sprintf("%s", $query_params['paymazon_payment_status']) : '';
		
		$sql = sprintf("INSERT INTO %s(order_id, request_id, paymazon_payment_code, paymazon_payment_shopid, paymazon_payment_id, paymazon_payment_status, paymazon_datetime_insert, paymazon_datetime_update) VALUES('%s', '%s', '%s', '%d', '%s', '%s', NOW(), NOW())",
			DB_PREFIX . 'payment_paymazon',
			$this->db->escape($order_id),
			$this->db->escape($request_id),
			$this->db->escape($query_params['paymazon_payment_code']),
			$this->db->escape($query_params['paymazon_payment_shopid']),
			$this->db->escape($query_params['paymazon_payment_id']),
			$this->db->escape($query_params['paymazon_payment_status'])
		);
		$sql_query = $this->db->query($sql);
		return $this->db->getLastId();
	}
	//-------------------------------------------------------
	public function getInstallmentTenors() {
		$installment_tenors = array();
		for ($i = 1; $i < 11; $i++) {
			$installment_tenors[] = array(
				'value'		=> sprintf("%02s", $i),
				'text'		=> sprintf("%02s %s", $i, "Month"),
			);
		}
		return $installment_tenors;
	}
   
}



