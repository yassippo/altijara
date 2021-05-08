<?php
class ModelExtensionShippingShippingcost extends Model {
	function getQuote($address) {

		$this->load->language('extension/shipping/shippingcost');

		$status = true;
		
		if(!empty($address['country_id'])){
			$country_id = $address['country_id'];
		} else {
			$country_id = '';
		}

		if(!empty($address['postcode'])){
			$postcode = $address['postcode'];
		} else {
			$postcode = '';
		}

		$this->load->model('vendor/vendor');
		$this->load->model('catalog/product');
		
		$allprice = 0;
		$products = $this->cart->getProducts();
		$shippingcost=array();
		
		foreach ($products as $product) {

			$product_total = 0;
			

			foreach ($products as $product_2) {
				if ($product_2['product_id'] == $product['product_id']) {
					$product_total += $product_2['quantity'];
				}
			}

			$vendorinfo = $this->model_vendor_vendor->getvendorpro($product['product_id']);
			
			if(isset($product['sellerdisplay'])){
				$sellerdisplay = $product['sellerdisplay'];
			} else {
				$sellerdisplay = '';
			}
			
			if(isset($product['vendor_ids'])){
				$vendor_ids = $product['vendor_ids'];
			} else {
				$vendor_ids = '';
			}

			if(isset($product['weight'])){
				$weight = $product['weight']*$product_total;
			} else {
				$weight = '';
			}

			$data['products'][] = array(
				'cart_id'   => $product['cart_id'],
				'name'      => $product['name'],
				'model'     => $product['model'],
			);

			
		
			$queryshippingcost = $this->db->query("SELECT * FROM " . DB_PREFIX . "shipping WHERE country_id = '" . $country_id . "' AND zip_from <= '". $postcode ."' AND zip_to >= '". $postcode ."' AND weight_from <= '". $weight ."' AND weight_to >= '". $weight ."' AND vendor_id = '" . $vendor_ids . "' LIMIT 0,1");
			
			foreach($queryshippingcost->rows as $result){
				$allprice += $result['price'];
					$shippingcost[$product['product_id']]=$this->tax->calculate($result['price'], $this->config->get('shipping_shippingcost_tax_class_id'), $this->config->get('config_tax'));
			}
			
			
		}

		$this->session->data['tmdshippingcost']=$shippingcost;	
		
		$method_data = array();

		if ($status && $allprice!=0) {
			$quote_data = array();

			$shipping_shippingcost = $this->config->get('shipping_shippingcost');

			 if(!empty($shipping_shippingcost[$this->config->get('config_language_id')]['title'])){
	            $shippingcost = $shipping_shippingcost[$this->config->get('config_language_id')]['title'];
	        } else {
	            $shippingcost = $this->language->get('text_description');
	        }

			$quote_data['shippingcost'] = array(
				'code'         => 'shippingcost.shippingcost',
				'title'        => $shippingcost,
				'cost'         => $this->tax->calculate($allprice, $this->config->get('shipping_shippingcost_tax_class_id'), $this->config->get('config_tax')),
				'tax_class_id' =>0,
				'text'         => $this->currency->format($this->tax->calculate($allprice, $this->config->get('shipping_shippingcost_tax_class_id'), $this->config->get('config_tax')), $this->session->data['currency'])
			);

			$method_data = array(
				'code'       => 'shippingcost',
				'title'      => $this->language->get('text_title'),
				'quote'      => $quote_data,
				'sort_order' => $this->config->get('shipping_shippingcost_sort_order'),
				'error'      => false
			);
		}

		return $method_data;
	}
}