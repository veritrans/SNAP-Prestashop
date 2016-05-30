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

		// error_log("SNAP TOKEN ==============="); //debugan
		// error_log($_GET['snap_token']); //debugan

		// Add snap JS libray
		// $this->context->controller->addJS('https://app.sandbox.veritrans.co.id/snap/assets/snap.js');

		$this->context->smarty->assign(array(
			'status' => $status,
			'snap_token' => $_GET['snap_token'],
			'this_path' => $this->module->getPathUri(),
			'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->module->name.'/'
		));

		$this->setTemplate('snappay.tpl');
	}

}


		