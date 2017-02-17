<?php

session_start();

class MidtransPaySuccessModuleFrontController extends ModuleFrontController
{
	public $ssl = true;

	/**
	 * @see FrontController::initContent()
	 */
	public function initContent()
	{
		$this->display_column_left = false;
		$this->display_column_right = false;
		parent::initContent();

		$cart = $this->context->cart;
		if (!$this->module->checkCurrency($cart))
			Tools::redirect('index.php?controller=order');
		$status = 'success';

		// new finish page
		if (version_compare(Configuration::get('PS_VERSION_DB'), '1.7') != -1) {
			$midtranspay = Module::getInstanceByName('midtranspay');
			$customer = new Customer((int)$cart->id_customer);
			$order_id = $_GET['order_id'];
			Tools::redirect('index.php?controller=order-confirmation&id_cart='.$order_id.'&id_module='.$midtranspay->id.'&id_order='.$order_id.'&key='.$customer->secure_key);
		}

		$this->context->smarty->assign(array(
			'order_id' => $cart->id,
			'status' => $status,
			'shop_name' => $this->context->shop->name,
			'this_path' => $this->module->getPathUri(),
			'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->module->name.'/'
		));

		if (version_compare(Configuration::get('PS_VERSION_DB'), '1.7') == -1)
			$this->setTemplate('notification.tpl');
		else
			$this->setTemplate('module:midtranspay/views/templates/front/notification17.tpl');	
	}

}


		