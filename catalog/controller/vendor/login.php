<?php
class ControllerVendorLogin extends Controller {
	private $error = array();
	public function index() {
		if ($this->vendor->isLogged()) {
			$this->response->redirect($this->url->link('vendor/dashboard', '', true));
		}
	
		$this->load->model('vendor/vendor');
		$this->load->language('vendor/login');

		$this->document->setTitle($this->language->get('heading_title'));

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
		
			if (isset($this->request->post['redirect']) && $this->request->post['redirect'] != $this->url->link('account/logout', '', true) && (strpos($this->request->post['redirect'], $this->config->get('config_url')) !== false || strpos($this->request->post['redirect'], $this->config->get('config_ssl')) !== false)) {
				$this->response->redirect(str_replace('&amp;', '&', $this->request->post['redirect']));
			} else {
				$this->response->redirect($this->url->link('vendor/dashboard', '', true));
			}
		}

		$data['breadcrumbs'] = array();
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_account'),
			'href' => $this->url->link('vendor/vendor', '', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_login'),
			'href' => $this->url->link('vendor/login', '', true)
		);

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_donot'] 					= $this->language->get('text_donot');
		$data['text_remember'] 					= $this->language->get('text_remember');
		$data['text_new_customer'] 				= $this->language->get('text_new_customer');
		$data['text_register'] 					= $this->language->get('text_register');
		$data['text_register_account'] 			= $this->language->get('text_register_account');
		$data['text_returning_customer'] 		= $this->language->get('text_returning_customer');
		$data['text_i_am_returning_customer'] 	= $this->language->get('text_i_am_returning_customer');
		$data['text_forgotten'] 				= $this->language->get('text_forgotten');
		$data['entry_email'] 					= $this->language->get('entry_email');
		$data['entry_password'] 				= $this->language->get('entry_password');
		$data['button_continue'] 				= $this->language->get('button_continue');
		$data['button_login'] 					= $this->language->get('button_login');

		if (isset($this->session->data['error'])) {
			$data['error_warning'] = $this->session->data['error'];
			unset($this->session->data['error']);
		} elseif (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['action'] 	= $this->url->link('vendor/login', '', true);
		$data['register'] 	= $this->url->link('vendor/vendor', '', true);
		$data['forgotten'] 	= $this->url->link('vendor/forgotten', '', true);
		
		if (isset($this->request->post['redirect']) && (strpos($this->request->post['redirect'], $this->config->get('config_url')) !== false || strpos($this->request->post['redirect'], $this->config->get('config_ssl')) !== false)) {
			$data['redirect'] = $this->request->post['redirect'];
		} elseif (isset($this->session->data['redirect'])) {
			$data['redirect'] = $this->session->data['redirect'];
			unset($this->session->data['redirect']);
		} else {
			$data['redirect'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		if (isset($this->request->post['email'])) {
			$data['email'] = $this->request->post['email'];
		} else {
			$data['email'] = '';
		}

		if (isset($this->request->post['password'])) {
			$data['password'] = $this->request->post['password'];
		} else {
			$data['password'] = '';
		}
		
		$data['column_left'] 	= $this->load->controller('common/column_left');
		$data['column_right'] 	= $this->load->controller('common/column_right');
		$data['content_top'] 	= $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] 		= $this->load->controller('common/footer');
		$data['header'] 		= $this->load->controller('common/header');
		
		$this->response->setOutput($this->load->view('vendor/login', $data));
		
	}
	protected function validate() {
		$vendor_info = $this->model_vendor_vendor->getVendorByEmail($this->request->post['email']);

		if ($vendor_info && !$vendor_info['status']) {
			$this->error['warning'] = $this->language->get('error_approved');
		}
		if (!$this->error) {
			if (!$this->vendor->login($this->request->post['email'], $this->request->post['password'])) {
				$this->error['warning'] = $this->language->get('error_login');
			}
		}
		return !$this->error;
	}
}
