<?php

session_start();

class MidtransPaySnappayModuleFrontController extends ModuleFrontController
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

		$status = 'token_exist';

		// TODO remove these, instruction_url_prefix is no longer needed
		$instruction_url_prefix = "//app.midtrans.com";
		// Configuration::get('MT_ENVIRONMENT') == 'production' ? 
		// 	$instruction_url_prefix = '//app.veritrans.co.id' : 
		// 	$instruction_url_prefix = '//app.sandbox.veritrans.co.id';

		// error_log("SNAP TOKEN ==============="); //debugan
		// error_log($_GET['snap_token']); //debugan

		// Add snap JS libray
		// $this->context->controller->addJS('https://app.sandbox.veritrans.co.id/snap/assets/snap.js');

		$moduleUrl = substr($this->context->link->getModuleLink('midtranspay','?'), 0, -1);
		// error_log($moduleUrl); // debugan

		$this->context->smarty->assign(array(
			'status' => $status,
			'snap_script_url' => $_GET['prod'] == "1" ? "https://app.midtrans.com/snap/snap.js" : "https://app.sandbox.midtrans.com/snap/snap.js",
			'instruction_url_prefix' => $instruction_url_prefix,
			'snap_token' => $_GET['snap_token'],
			'this_path' => $this->module->getPathUri(),
			'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->module->name.'/',
			'shop_url' => __PS_BASE_URI__,
			'moduleUrl' => $moduleUrl
		));
		$this->setTemplate('snappay.tpl');
	}

}


		