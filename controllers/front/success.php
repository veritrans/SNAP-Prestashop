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

		// if 1.7, go to new finish page
		if (version_compare(Configuration::get('PS_VERSION_DB'), '1.7') != -1) {
			// If async payment denied (CIMB, klikpay, etc), redir to failure page
			if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_GET['id']) ) {
				parse_str(file_get_contents("php://input"), $data);
				if (json_decode(urldecode($data['response']),true)['transaction_status'] == 'deny') { 
					Tools::redirect( $this->context->link->getModuleLink('midtranspay','failure') ); exit();
				}
				$order_id = json_decode(urldecode($data['response']),true)['order_id'];
			}else if (isset($_GET['id'])) {
				Veritrans_Config::$serverKey = Configuration::get('MT_SERVER_KEY');
				Veritrans_Config::$isProduction = Configuration::get('MT_ENVIRONMENT') == 'production' ? true : false;
				$status = Veritrans_Transaction::status($_GET['id']);
				if($status->transaction_status == 'deny'){
					Tools::redirect( $this->context->link->getModuleLink('midtranspay','failure') ); exit();
				}
				$order_id = $status->order_id;
			}else {
				$order_id = $_GET['order_id'];	
			}
			$midtranspay = Module::getInstanceByName('midtranspay');
			$customer = new Customer((int)$cart->id_customer);
			Tools::redirect('index.php?controller=order-confirmation&id_cart='.$order_id.'&id_module='.$midtranspay->id.'&id_order='.$order_id.'&key='.$customer->secure_key);
		}

		// If async payment denied (CIMB, klikpay, etc), redir to failure page
		if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_GET['id']) ) {
			parse_str(file_get_contents("php://input"), $data);
			if (json_decode(urldecode($data['response']),true)['transaction_status'] == 'deny') { 
				Tools::redirect( $this->context->link->getModuleLink('midtranspay','failure').'?&status_code=202' ); exit();
			}
		};
		// If BCA klikpay, get status then redirect accordingly.
		if (isset($_GET['id'])){
			Veritrans_Config::$serverKey = Configuration::get('MT_SERVER_KEY');
			Veritrans_Config::$isProduction = Configuration::get('MT_ENVIRONMENT') == 'production' ? true : false;
			$status = Veritrans_Transaction::status($_GET['id']);
			if($status->transaction_status == 'settlement'){
				Tools::redirect( $this->context->link->getModuleLink('midtranspay','success').'?&status_code=200' ); exit();
			} else {
				Tools::redirect( $this->context->link->getModuleLink('midtranspay','failure').'?&status_code=202' ); exit();
			}
		}
		$this->context->smarty->assign(array(
			'order_id' => $cart->id,
			'status' => $status,
			'shop_name' => $this->context->shop->name,
			'this_path' => $this->module->getPathUri(),
			'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->module->name.'/'
		));

		if (version_compare(Configuration::get('PS_VERSION_DB'), '1.7') == -1)
			$this->setTemplate('payment_result.tpl');
	}

}


		