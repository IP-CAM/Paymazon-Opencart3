<?php

class ModelExtensionPaymentPaymazon extends Model {

    public function createPaymazonDataTable() {
		$sql = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "payment_paymazon` (
			`seq` INT(11) NOT NULL AUTO_INCREMENT,
			`order_id` VARCHAR(128) NOT NULL,
			`request_id` VARCHAR(64) NOT NULL,
			`paymazon_payment_code` VARCHAR(32) NOT NULL,
			`paymazon_payment_id` VARCHAR(128) NOT NULL,
			`paymazon_payment_status` VARCHAR(16) NOT NULL,
			`paymazon_datetime_insert` DATETIME NOT NULL,
			`paymazon_datetime_update` DATETIME NOT NULL,
			PRIMARY KEY (`seq`),
			UNIQUE INDEX `order_id_paymazon_payment_id` (`order_id`, `paymazon_payment_id`),
			INDEX `order_id` (`order_id`)
		)
		COMMENT='Table for paymazon transactions'
		ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
		$this->db->query($sql);
    }
	public function deletePaymazonDataTable() {
		//$sql = "DROP TABLE IF EXISTS `" . DB_PREFIX . "payment_paymazon`;";
		//$this->db->query($sql);
		return true;
    }
	
	
	
	//==========
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



