<?php
class ModelVendorCommunication extends Model {
	
	public function Addmessagereply($data, $chat_id) {
	if(isset($data['reply_to'])){
	$customerquery = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer WHERE email = '" . $data['reply_to'] . "'");
	$vendorquery = $this->db->query("SELECT * FROM " . DB_PREFIX . "vendor WHERE email = '" . $data['reply_to'] . "'");
		if(!empty($customerquery->row)){
		$customer_id=$customerquery->row['customer_id'];
		}else{
			$customer_id='0';
		}
		if(!empty($vendorquery->row)){
		$vendor_id=$vendorquery->row['vendor_id'];	
		}else{
			$vendor_id='0';
		}
	

	}
	 $this->db->query("INSERT INTO " . DB_PREFIX . "vendor_adminchat SET admin = '" . $this->user->getId() . "', subject = '" . $this->db->escape($data['subject']) . "',message = '" . $this->db->escape($data['message']) . "', attachimage = '" . $this->db->escape($data['attachimage']) . "', chat_id = '" . (int)$chat_id . "', user='admin', type='outbox', date_added = NOW()");	
	 $this->db->query("INSERT INTO " . DB_PREFIX . "vendor_customerchat SET admin = '" . $this->user->getId() . "', customer_id='".(int)$customer_id."', subject = '" . $this->db->escape($data['subject']) . "',message = '" . $this->db->escape($data['message']) . "', attachimage = '" . $this->db->escape($data['attachimage']) . "', chat_id = '" . (int)$chat_id . "', user='admin', type='inbox', date_added = NOW()");	
	 $this->db->query("INSERT INTO " . DB_PREFIX . "vendor_vendorchat SET admin = '" . $this->user->getId() . "', vendor_id='".(int)$vendor_id."', subject = '" . $this->db->escape($data['subject']) . "',message = '" . $this->db->escape($data['message']) . "', attachimage = '" . $this->db->escape($data['attachimage']) . "', chat_id = '" . (int)$chat_id . "', user='admin', type='inbox', date_added = NOW()");	
		
		//$chat_id = $this->db->getLastId();

	 /// Admin Mail custmer vendor
		$this->load->model('vendor/mail');
		$sellertype = 'admin_reply_email';
		
		$mailinfo = $this->model_vendor_mail->getMailInfo($sellertype);
		
		/*$querymail = $this->db->query("SELECT *  FROM " . DB_PREFIX . "vendor WHERE vendor_id = '" . $this->vendor->getId() . "'");
		$vendor_mail=$querymail->row['email'];
		$vendor_name=$querymail->row['firstname'];
		
		$querycust= $this->db->query("SELECT *  FROM " . DB_PREFIX . "customer WHERE customer_id = '" . $customer_id . "'");
		$custmail=$querycust->row['email'];*/
		
		/*Status Enabled*/	
		if(isset($mailinfo['status'])){
			
			
			$find = array(
				///'{name}',
				'{from}',
				'{subject}',
				'{message}'										
													
			);
			$replace = array(
				//'from'	=> 'name',
				'from'	=> $this->config->get('config_email'),
				'subject'	=> $data['subject'],
				'message' 		=> $data['message']
		
			);
			

			$subject = str_replace(array("\r\n", "\r", "\n"), '', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '', trim(str_replace($find, $replace, $mailinfo['subject']))));

			$message = str_replace(array("\r\n", "\r", "\n"), '', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '', trim(str_replace($find, $replace, $mailinfo['message']))));
			
			$mail = new Mail($this->config->get('config_mail_engine'));
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
			$mail->smtp_username = $this->config->get('config_mail_smtp_username');
			$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
			$mail->smtp_port = $this->config->get('config_mail_smtp_port');
			$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

			$mail->setTo($data['reply_to']);
			$mail->setFrom($this->config->get('config_email'));
			$mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
			$mail->setSubject($subject);
			$mail->setHtml(html_entity_decode($message));
			$mail->send();
					
		}


	}
	
	public function getMessageHistorys($chat_id){
		$sql = "SELECT * FROM " . DB_PREFIX . "vendor_adminchat WHERE chat_id='".(int)$chat_id."' ORDER BY date_added ASC";
		$query = $this->db->query($sql);
		return $query->rows;
	}
	public function AddTrash($chat_id){
		 $this->db->query("UPDATE " . DB_PREFIX . "vendor_chat SET trashadmin = '1'  WHERE chat_id = '" . (int)$chat_id . "'");	
		 	
	}
	public function AddSendTrash($chat_id){
		 $this->db->query("UPDATE " . DB_PREFIX . "vendor_chat SET trashadmin = '1'  WHERE chat_id = '" . (int)$chat_id . "'");	
	}

	public function getMessages($data = array()){
		//$sql = "SELECT * FROM " . DB_PREFIX . "vendor_chat WHERE chat_id<>0";
$sql = "SELECT *,vc.email as customermail,vc.date_added as date_added FROM " . DB_PREFIX . "vendor_chat vc LEFT JOIN " . DB_PREFIX . "vendor v ON (vc.vendor_id = v.vendor_id) WHERE vc.chat_id<>0";

		$sort_data = array(
			'vc.subject'
		);


		if (isset($data['filter_subject'])){
		 	$sql .=" and vc.subject like '".$this->db->escape($data['filter_subject'])."%'";
		}
		if (isset($data['filter_mailfrom'])){
		 	$sql .=" and vc.email like '".$this->db->escape($data['filter_mailfrom'])."%'";
		}
		if (isset($data['filter_date_added'])){
		 	$sql .=" and vc.date_added like '".$this->db->escape($data['filter_date_added'])."%'";
		}
		if (isset($data['filter_mailto'])){
		 	$sql .=" and v.email like '".$this->db->escape($data['filter_mailto'])."%'";
		}

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
		 	$sql .= " ORDER BY " . $data['sort'];
		} else {
		 	$sql .= " ORDER BY vc.date_added";
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

	public function getsendmessages($data){
		//$sql = "SELECT * FROM " . DB_PREFIX . "vendor_chat WHERE vendor_id='".$this->vendor->getId()."' AND trashadmin='0'";
	$sql = "SELECT *,vc.subject as subjectcust, vc.date_added as date_addedcust, vc.customer_id as customer_id, vc.vendor_id as vendor_id FROM " . DB_PREFIX . "vendor_chat vc LEFT JOIN " . DB_PREFIX . "vendor_vendorchat vh ON (vc.chat_id = vh.chat_id)  WHERE vc.chat_id<>0 AND vh.user='admin'";
		$sql .= " GROUP BY vc.chat_id";
	
		$sort_data = array(
			'subjectcust'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
		 	$sql .= " ORDER BY " . $data['sort'];
		} else {
		 	$sql .= " ORDER BY date_addedcust";
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

	/*public function getsendMessages($data){
		$sql = "SELECT * FROM " . DB_PREFIX . "vendor_vendorchat WHERE vendor_id='".$this->vendor->getId()."' AND user='seller' ORDER BY date_added DESC";
		$query = $this->db->query($sql);
		return $query->rows;
	}*/
	

	public function getTrashMessages($data){
		$sql = "SELECT * FROM " . DB_PREFIX . "vendor_chat WHERE chat_id<>0 ORDER BY date_added DESC";
		
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

	public function getTotalmessages($data){
		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "vendor_chat vc LEFT JOIN " . DB_PREFIX . "vendor v ON (vc.vendor_id = v.vendor_id) WHERE vc.chat_id<>0";

		if (isset($data['filter_subject'])){
		 	$sql .=" and vc.subject like '".$this->db->escape($data['filter_subject'])."%'";
		}
		if (isset($data['filter_mailfrom'])){
		 	$sql .=" and vc.email like '".$this->db->escape($data['filter_mailfrom'])."%'";
		}
		if (isset($data['filter_date_added'])){
		 	$sql .=" and vc.date_added like '".$this->db->escape($data['filter_date_added'])."%'";
		}
		if (isset($data['filter_mailto'])){
		 	$sql .=" and v.email like '".$this->db->escape($data['filter_mailto'])."%'";
		}
		$query = $this->db->query($sql);
		return $query->row['total'];
	}

	public function getTotaltrashmessage($data){
		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "vendor_chat WHERE chat_id<>0";
		$query = $this->db->query($sql);
		return $query->row['total'];
	}
	public function getTotalreplymessage($chat_id){
		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "vendor_adminchat WHERE chat_id='".(int)$chat_id."' AND (user='customer' or user='vendor')";
		$query = $this->db->query($sql);
		return $query->row['total'];
	}
	public function getTotalreplyseller($chat_id){
		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "vendor_adminchat WHERE chat_id='".(int)$chat_id."' AND user='admin'";
		$query = $this->db->query($sql);
		return $query->row['total'];
	}
	public function getcountselersendmessage($data){
		//$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "vendor_vendorchat WHERE vendor_id='".$this->vendor->getId()."' AND user='seller'";
		$sql = "SELECT  COUNT(*) AS total FROM " . DB_PREFIX . "vendor_chat vc LEFT JOIN " . DB_PREFIX . "vendor_vendorchat vh ON (vc.chat_id = vh.chat_id)  WHERE vh.chat_id<>0 AND vh.user='admin'";

		$query = $this->db->query($sql);
		return $query->row['total'];
	}

	public function getTotalsendmessages($data){
		//$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "vendor_chat WHERE vendor_id='".$this->vendor->getId()."' AND trashadmin='0'";
		$sql = "SELECT  COUNT(DISTINCT vc.chat_id) AS total FROM " . DB_PREFIX . "vendor_chat vc LEFT JOIN " . DB_PREFIX . "vendor_vendorchat vh ON (vc.chat_id = vh.chat_id)  WHERE vc.chat_id<>0 AND vh.user='admin'";

		$query = $this->db->query($sql);
		return $query->row['total'];
	}


	public function deletemessages($chat_id){
		$this->db->query("DELETE FROM " . DB_PREFIX . "vendor_chat WHERE chat_id = '" . (int)$chat_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "vendor_adminchat WHERE chat_id = '" . (int)$chat_id . "'");
		//$this->db->query("DELETE FROM " . DB_PREFIX . "vendor_vendorchat WHERE chat_id = '" . (int)$chat_id . "'");
	}

	public function getCustomer($customer_id){
		$sql = "SELECT * FROM " . DB_PREFIX . "customer WHERE customer_id='".(int)$customer_id."'";
		$query = $this->db->query($sql);
		return $query->row;
	}
	public function getVendor($vendor_id){
		$sql = "SELECT * FROM " . DB_PREFIX . "vendor WHERE vendor_id='".(int)$vendor_id."'";
		$query = $this->db->query($sql);
		return $query->row;
	}
	public function getcustomermail($chat_id){
		$sql = "SELECT * FROM " . DB_PREFIX . "vendor_chat WHERE chat_id='".(int)$chat_id."'";
		$query = $this->db->query($sql);
		return $query->row;
	}

	
	
	
}
?>