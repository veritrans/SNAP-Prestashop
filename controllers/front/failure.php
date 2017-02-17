<?php

session_start();

class MidtransPayFailureModuleFrontController extends ModuleFrontController
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
		global $smarty;

		// if payment failed, get order_id to generate reorder/re-checkout URL, so that customer could do re-checkout
		if (null !==Tools::getValue('order_id') && '' !== Tools::getValue('order_id') ){
			$order_id = Tools::getValue('order_id');
		}
		$cart = $this->context->cart;
		$status = 'failure';

		$this->context->smarty->assign(array(
			'shop_name' => $this->context->shop->name,
			'status' => $status,
			'order_id' => $cart->id,
			'this_path' => $this->module->getPathUri(),
			'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->module->name.'/'
		));

		if (version_compare(Configuration::get('PS_VERSION_DB'), '1.7') == -1)
			$this->setTemplate('notification.tpl');
		else
			$this->setTemplate('module:midtranspay/views/templates/front/notification17.tpl');
	}

}


		