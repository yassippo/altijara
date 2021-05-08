<?php
class ControllerVendorChangeSuccess extends Controller {
	public function index() {
		if (!$this->vendor->isLogged()) {
			$this->response->redirect($this->url->link('vendor/login', '', true));
		}
		$this->load->language('vendor/changesuccess');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_account'),
			'href' => $this->url->link('vendor/edit', '', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_success'),
			'href' => $this->url->link('vendor/changesuccess')
		);

		$data['heading_title'] = $this->language->get('heading_title');
		/*updated code start*/
		$data['text_message'] = sprintf($this->language->get('text_message'), $this->url->link('information/contact'));
        /*updated code end*/

		$this->load->model('account/customer_group');

		$data['button_continue'] = $this->language->get('button_continue');

		if ($this->vendor->isLogged()) {
			$data['continue'] = $this->url->link('common/home');
		} else {
			$data['continue'] = $this->url->link('vendor/login', '', true);
		}

		$data['column_left'] 	= $this->load->controller('common/column_left');
		$data['column_right'] 	= $this->load->controller('common/column_right');
		$data['content_top'] 	= $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] 		= $this->load->controller('common/footer');
		$data['header'] 		= $this->load->controller('common/header');

		
		$this->response->setOutput($this->load->view('vendor/changesuccess', $data));
	}
}