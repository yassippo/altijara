<?php
class ModelVendorincome extends Model {	
	
	public function addAmount($data) {
		
		if(isset($data['amount'])){
			$amount = $data['amount'];
		} else {
			$amount ='';
		}
		
		$sql="INSERT INTO " . DB_PREFIX . "vendor_amount_pay set vendor_id='".(int)$data['vendor_id']."',payment_method='".$this->db->escape($data['payment_method'])."',amount='".(float)$amount."',comment='".$this->db->escape($data['comment'])."',date_added=NOW()";
		$this->db->query($sql);
		return $amount;
	}
		
	public function getTotal($data,$vendor_id){
		
		$implode = array();

		$vendorearnstatus = $this->config->get('vendor_earnpayment_status');
		$defaultstatus = $this->config->get('config_complete_status');
	
		if(!empty($vendorearnstatus)){
			foreach ($vendorearnstatus as $order_status_id) {
				$implode[] = "'" . (int)$order_status_id . "'";
				}
		} else {
			foreach ($defaultstatus as $order_status_id) {
			$implode[] = "'" . (int)$order_status_id . "'";
			}
		}	
		
		$sql = "SELECT sum(total) AS total FROM " . DB_PREFIX . "vendor_order_product WHERE order_product_id<>0";
						
			
		$sql .= " AND vendor_id ='".(int)$vendor_id. "' AND order_status_id IN(" . implode(",", $implode) . ")";
		
		$query = $this->db->query($sql);
		return $query->row['total'];
	}

	public function getTotalCommission($data,$vendor_id){
		$implode = array();
		$vendorearnstatus = $this->config->get('vendor_earnpayment_status');
		$defaultstatus = $this->config->get('config_complete_status');
	
		if(!empty($vendorearnstatus)){
			foreach ($vendorearnstatus as $order_status_id) {
				$implode[] = "'" . (int)$order_status_id . "'";
				}
		} else {
			foreach ($defaultstatus as $order_status_id) {
			$implode[] = "'" . (int)$order_status_id . "'";
			}
		}

		$sql = "SELECT sum(totalcommission) AS total FROM " . DB_PREFIX . "vendor_order_product WHERE order_product_id<>0";
						
		$sql .= " AND vendor_id ='".(int)$vendor_id. "'";

				
		$sql .= " AND vendor_id ='".(int)$vendor_id. "' AND order_status_id IN(" . implode(",", $implode) . ")";
		
		$query = $this->db->query($sql);
		return $query->row['total'];
	}
	
	public function getPay($vendor_id){
		
		$sql = "SELECT * FROM " . DB_PREFIX . "vendor_amount_pay WHERE vendor_id='".(int)$vendor_id."'";
		$query = $this->db->query($sql);
		return $query->row;
	}
	
	public function getAmount($vendor_id){
		$sql = "SELECT sum(amount) AS total FROM " . DB_PREFIX . "vendor_amount_pay WHERE vendor_id='".(int)$vendor_id."'";
		$query = $this->db->query($sql);
		return $query->row['total'];
	}
				
 	public function getIncomes($data=array()){

		$implode = array();

		$vendorearnstatus = $this->config->get('vendor_earnpayment_status');
		$defaultstatus = $this->config->get('config_complete_status');
	
		if(!empty($vendorearnstatus)){
			foreach ($vendorearnstatus as $order_status_id) {
				$implode[] = "'" . (int)$order_status_id . "'";
				}
		} else {
			foreach ($defaultstatus as $order_status_id) {
			$implode[] = "'" . (int)$order_status_id . "'";
			}
		}
			
		$sql="SELECT * FROM " . DB_PREFIX . "vendor v LEFT JOIN " . DB_PREFIX . "vendor_order_product vop ON (v.vendor_id = vop.vendor_id) WHERE v.vendor_id<>0 And  vop.order_status_id IN(" . implode(",", $implode) . ")";
		
		/* 11 02 2020 */
		if (!empty($data['filter_vendor'])){
		 	$sql .=" and v.vendor_id='".$this->db->escape($data['filter_vendor'])."'";
		}
		if (!empty($data['filter_date_added_from'])) {
			$sql .= " AND DATE(vop.date_added) >= '" . $data['filter_date_added_from'] . "'";
		}

		if (!empty($data['filter_date_added_to'])) {
			$sql .= " AND DATE(vop.date_added) <= '" . $data['filter_date_added_to'] . "'";
		}
		
		$sql .= " group by v.vendor_id";
		
		$sort_data = array(
			'v.vendor_id'
		);
		
		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
		 	$sql .= " ORDER BY " . $data['sort'];
		} else {
		 	$sql .= " ORDER BY v.vendor_id";
		}
		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}
		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}
			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}
			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}
		$query = $this->db->query($sql);
		return $query->rows;	
 	}
 	public function getTotalIncome($data=array()) {
	
		$implode = array();

		$vendorearnstatus = $this->config->get('vendor_earnpayment_status');
		$defaultstatus = $this->config->get('config_complete_status');
	
		if(!empty($vendorearnstatus)){
			foreach ($vendorearnstatus as $order_status_id) {
				$implode[] = "'" . (int)$order_status_id . "'";
				}
		} else {
			foreach ($defaultstatus as $order_status_id) {
			$implode[] = "'" . (int)$order_status_id . "'";
			}
		}
		
		$sql = "SELECT *  FROM " . DB_PREFIX . "vendor v LEFT JOIN " . DB_PREFIX . "vendor_order_product vop ON (v.vendor_id = vop.vendor_id) WHERE v.vendor_id<>0 And  vop.order_status_id IN(" . implode(",", $implode) . ")";
		
		
		if (!empty($data['filter_vendor'])){
		 	$sql .=" and v.vendor_id='".$this->db->escape($data['filter_vendor'])."'";
			}
		
		if (!empty($data['filter_date_added_from'])) {
			$sql .= " AND DATE(vop.date_added) >= '" . $data['filter_date_added_from'] . "'";
		}

		if (!empty($data['filter_date_added_to'])) {
			$sql .= " AND DATE(vop.date_added) <= '" . $data['filter_date_added_to'] . "'";
		}
		
		$sql .=" group by v.vendor_id";
		$query = $this->db->query($sql);
		if(isset($query->num_rows)){
		return $query->num_rows;
		
		}
	}

	public function getVendorTotal($vendor_id){
	
		$implode = array();

		$vendorearnstatus = $this->config->get('vendor_earnpayment_status');
		$defaultstatus = $this->config->get('config_complete_status');
	
		if(!empty($vendorearnstatus)){
			foreach ($vendorearnstatus as $order_status_id) {
				$implode[] = "'" . (int)$order_status_id . "'";
				}
		} else {
			foreach ($defaultstatus as $order_status_id) {
			$implode[] = "'" . (int)$order_status_id . "'";
			}
		}		
		$sql = "SELECT sum(total) AS total FROM " . DB_PREFIX . "vendor_order_product WHERE vendor_id ='".(int)$vendor_id. "' And order_status_id IN(" . implode(",", $implode) . ")";
								
		$query = $this->db->query($sql);
		return $query->row['total'];
	}

	public function getTotalAmount($vendor_id){
		$implode = array();

		$vendorearnstatus = $this->config->get('vendor_earnpayment_status');
		$defaultstatus = $this->config->get('config_complete_status');
	
		if(!empty($vendorearnstatus)){
			foreach ($vendorearnstatus as $order_status_id) {
				$implode[] = "'" . (int)$order_status_id . "'";
				}
		} else {
			foreach ($defaultstatus as $order_status_id) {
			$implode[] = "'" . (int)$order_status_id . "'";
			}
		}
		$sql = "SELECT sum(totalcommission) AS total FROM " . DB_PREFIX . "vendor_order_product WHERE order_product_id<>0";
						
		$sql .= " AND vendor_id ='".(int)$vendor_id. "'";
				
		$sql .= " AND vendor_id ='".(int)$vendor_id. "' AND order_status_id IN(" . implode(",", $implode) . ")";
		
		$query = $this->db->query($sql);
		return $query->row['total'];
	}
	
	public function getShippingAmount($vendor_id){
		$implode = array();

		foreach ($this->config->get('vendor_earnpayment_status') as $order_status_id) {
			$implode[] = "'" . (int)$order_status_id . "'";
		}
		
		$sql = "SELECT * FROM " . DB_PREFIX . "vendor_order_product  WHERE vendor_id='".(int)$vendor_id."' AND  order_status_id IN(" . implode(",", $implode) . ")";
		
		$query = $this->db->query($sql);
		return $query->row;
	}
	
}
?>