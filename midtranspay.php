<?php

if (!defined('_PS_VERSION_'))
	exit;
// TODO refactor code, get rid of installment etc.
// TODO refactor backend config fields, getrid of enabled payments etc.

// TODO uncomment these, use the real snap php library class (make sure to do this on other file too)
require_once('library/veritrans/Veritrans.php');
require_once ('library/veritrans/Veritrans/Notification.php');
require_once ('library/veritrans/Veritrans/Transaction.php');

// TODO remove theese
// require_once(dirname(__FILE__).'/../veritranspay/library/veritrans/Veritrans.php');
// require_once(dirname(__FILE__).'/../veritranspay/library/veritrans/Veritrans/Notification.php');
// require_once(dirname(__FILE__).'/../veritranspay/library/veritrans/Veritrans/Transaction.php');

class MidtransPay extends PaymentModule
{
	private $_html = '';
	private $_postErrors = array();

	public $midtrans_merchant_id;
	public $midtrans_merchant_hash;
	public $midtrans_kurs;
	public $midtrans_convenience_fee;
	public $midtrans_client_key;
	public $midtrans_server_key;
	public $midtrans_api_version;
	public $midtrans_installments;
	public $midtrans_3d_secure;
	public $midtrans_payment_type;
	public $midtrans_payment_success_status_mapping;
	public $midtrans_payment_failure_status_mapping;
	public $midtrans_payment_challenge_status_mapping;
	public $midtrans_environment;

	public $config_keys;

	public function __construct()
	{
		$this->name = 'midtranspay';
		$this->tab = 'payments_gateways';
		$this->version = '1.0';
		$this->author = 'Midtrans';
		$this->bootstrap = true;
		
		$this->currencies = true;
		$this->currencies_mode = 'checkbox';
		$this->midtrans_convenience_fee = 0;

		// key length must be between 0-32 chars to maintain compatibility with <= 1.5
		$this->config_keys = array(			
			'MT_DISPLAY_TITLE',
			'MT_CLIENT_KEY',
			'MT_SERVER_KEY',
			'MT_API_VERSION',
			'MT_PAYMENT_TYPE',
			'MT_3D_SECURE',
			'MT_KURS',
			'MT_CONVENIENCE_FEE',
			'MT_PAYMENT_SUCCESS_STATUS_MAP',
			'MT_PAYMENT_FAILURE_STATUS_MAP',
			'MT_PAYMENT_CHALLENGE_STATUS_MAP',
			'MT_ENVIRONMENT',
			'MT_ENABLED_CREDIT_CARD',
			'MT_ENABLED_CIMB',
			'MT_ENABLED_MANDIRI',
			'MT_ENABLED_PERMATAVA',
			'MT_ENABLED_BRIEPAY',
			'MT_ENABLED_TELKOMSEL_CASH',
			'MT_ENABLED_XL_TUNAI',
			'MT_ENABLED_MANDIRI_BILL',
			'MT_ENABLED_BBM_MONEY',
			'MT_ENABLED_INDOMARET',
			'MT_ENABLED_INDOSAT_DOMPETKU',
			'MT_ENABLED_MANDIRI_ECASH',
			'MT_SANITIZED',
			'MT_ENABLE_INSTALLMENT',
			'MT_ENABLED_BNI_INSTALLMENT',
			'MT_ENABLED_MANDIRI_INSTALLMENT',
			'MT_INSTALLMENTS_BNI',
			'MT_INSTALLMENTS_MANDIRI'
			);

		foreach (array('BNI', 'MANDIRI') as $bank) {
			foreach (array(3, 6, 12) as $months) {
				array_push($this->config_keys, 'MT_INSTALLMENTS_' . $bank . '_' . $months);
			}
		}

		$config = Configuration::getMultiple($this->config_keys);

		foreach ($this->config_keys as $key) {
			if (isset($config[$key]))
				$this->{strtolower($key)} = $config[$key];
		}
		
		
		if (isset($config['MT_KURS']))
			$this->midtrans_kurs = $config['MT_KURS'];
		else
			Configuration::set('MT_KURS', 10000);
		
		Configuration::set('MT_API_VERSION', 2);
		Configuration::set('MT_PAYMENT_TYPE','vtweb');

		if (!isset($config['MT_DISPLAY_TITLE']))
			Configuration::set('MT_DISPLAY_TITLE', "Online Payment via Midtrans");	
		if (!isset($config['MT_SANITIZED']))
			Configuration::set('MT_SANITIZED', 1);	
		if (!isset($config['MT_3D_SECURE']))
			Configuration::set('MT_3D_SECURE', 1);
		if (!isset($config['MT_ENABLED_CREDIT_CARD']))
			Configuration::set('MT_ENABLED_CREDIT_CARD', 0);
		if (!isset($config['MT_ENABLED_CIMB']))
			Configuration::set('MT_ENABLED_CIMB', 0);		
		if (!isset($config['MT_ENABLED_MANDIRI']))
			Configuration::set('MT_ENABLED_MANDIRI', 0);		
		if (!isset($config['MT_ENABLED_PERMATAVA']))
			Configuration::set('MT_ENABLED_PERMATAVA', 0);
		if (!isset($config['MT_ENABLED_BRIEPAY']))
			Configuration::set('MT_ENABLED_BRIEPAY', 0);
		if (!isset($config['MT_ENABLED_TELKOMSEL_CASH']))
			Configuration::set('MT_ENABLED_TELKOMSEL_CASH', 0);
		if (!isset($config['MT_ENABLED_XL_TUNAI']))
			Configuration::set('MT_ENABLED_XL_TUNAI', 0);
		if (!isset($config['MT_ENABLED_MANDIRI_BILL']))
			Configuration::set('MT_ENABLED_MANDIRI_BILL', 0);
		if (!isset($config['MT_ENABLED_BBM_MONEY']))
			Configuration::set('MT_ENABLED_BBM_MONEY', 0);
		if (!isset($config['MT_ENABLED_INDOMARET']))
			Configuration::set('MT_ENABLED_INDOMARET', 0);
		if (!isset($config['MT_ENABLED_INDOSAT_DOMPETKU']))
			Configuration::set('MT_ENABLED_INDOSAT_DOMPETKU', 0);
		if (!isset($config['MT_ENABLED_MANDIRI_ECASH']))
			Configuration::set('MT_ENABLED_MANDIRI_ECASH', 0);
		parent::__construct();

		$this->displayName = $this->l('Midtrans Pay');
		$this->description = $this->l('Accept payments for your products via Midtrans payment gateway.');
		$this->confirmUninstall = $this->l('Are you sure about uninstalling Midtrans pay?');
		
		
		if (!count(Currency::checkPaymentCurrencies($this->id)))
			$this->warning = $this->l('No currency has been set for this module.');

		// Retrocompatibility
		$this->initContext();
	}

	public function isOldPrestashop()
	{
		return version_compare(Configuration::get('PS_VERSION_DB'), '1.5') == -1;
	}

	public function install()
	{
		// create a new order state for Midtrans, since Prestashop won't assign order ID unless it is validated,
		// and no default order states matches the state we want. Assigning order_id with uniqid() will confuse
		// users in the future
		$order_state = new OrderStateCore();
		$order_state->name = array((int)Configuration::get('PS_LANG_DEFAULT') => 'Awaiting Midtrans payment');;
		$order_state->module_name = 'midtranspay';
		if ($this->isOldPrestashop()) {
			$order_state->color = '#0000FF';
		} else
		{
			$order_state->color = 'RoyalBlue';
		}
		
		$order_state->unremovable = false;
		$order_state->add();

		Configuration::updateValue('MT_ORDER_STATE_ID', $order_state->id);
		Configuration::updateValue('MT_API_VERSION', 2);

		if (!parent::install() || 
			!$this->registerHook('payment') ||
			!$this->registerHook('header') ||
			!$this->registerHook('backOfficeHeader') ||
			!$this->registerHook('orderConfirmation'))
			return false;
		
		include_once(_PS_MODULE_DIR_ . '/' . $this->name . '/mtpay_install.php');
		$mtpay_install = new MidtransPayInstall();
		$mtpay_install->createTable();

		return true;
	}

	public function uninstall()
	{
		$status = true;

		$midtrans_payment_waiting_order_state_id = Configuration::get('MT_ORDER_STATE_ID');
		if ($midtrans_payment_waiting_order_state_id)
		{
			$order_state = new OrderStateCore($midtrans_payment_waiting_order_state_id);
			$order_state->delete();
		}
		
		
		if (!parent::uninstall())
			$status = false;
		return $status;
	}

	private function _postValidation()
	{
		if (Tools::isSubmit('btnSubmit'))
		{
			if (Tools::getValue('MT_API_VERSION') == 2)
			{
				if (!Tools::getValue('MT_CLIENT_KEY'))
					$this->_postErrors[] = $this->l('Client Key is required.');
				if (!Tools::getValue('MT_SERVER_KEY'))
					$this->_postErrors[] = $this->l('Server Key is required.');
			} 

			// validate conversion rate existence
			if (!Currency::exists('IDR', null) && !Tools::getValue('MT_KURS'))
			{
				$this->_postErrors[] = $this->l('Currency conversion rate must be filled when IDR is not installed in the system.');
			}
		}
	}

	private function _postProcess()
	{
		if (Tools::isSubmit('btnSubmit'))
		{
			foreach ($this->config_keys as $key) {
				Configuration::updateValue($key, Tools::getValue($key));
			}
		}
		$this->_html .= '<div class="alert alert-success conf confirm"> '.$this->l('Settings updated').'</div>';
	}

	private function _displayMidtransPay()
	{
		if (version_compare(Configuration::get('PS_VERSION_DB'), '1.5') == -1)
		{
			$output = $this->context->smarty->fetch(__DIR__ . '/views/templates/hook/infos.tpl');
			$this->_html .= $output;
		} else
		{
			$this->_html .= $this->display(__FILE__, 'infos.tpl');
		}
	}

	private function _displayMidtransPayOld()
	{
		$this->_html .= '<img src="../modules/midtranspay/Midtrans.png" style="float:left; margin-right:15px;"><b>'.$this->l('This module allows payment via midtrans.').'</b><br/><br/>
		'.$this->l('Payment via midtrans.').'<br /><br /><br />';
	}

	private function _displayForm()
	{
		if (version_compare(Configuration::get('PS_VERSION_DB'), '1.5') == -1) {
			// retrocompatibility with Prestashop 1.4
			$this->_displayFormOld();
		} else
		{
			$this->_displayFormNew();
		}
		
	}

	public function getConfigFieldsValues()
	{
		$result = array();
		foreach ($this->config_keys as $key) {
			$result[$key] = Tools::getValue($key, Configuration::get($key));
		}
		//error_log('message fields_value'); // debug
		//error_log(print_r($result,true)); // debug
		return $result;
	}

	private function _displayFormNew()
	{

		$order_states = array();
		foreach (OrderState::getOrderStates($this->context->language->id) as $state) {
			array_push($order_states, array(
				'id_option' => $state['id_order_state'],
				'name' => $state['name']
				)
			);
		}
		
		$installments_options = array();
		foreach (array('BNI', 'MANDIRI') as $bank) {
			$installments_options[$bank] = array();
			foreach (array(3, 6, 12) as $months) {
				array_push($installments_options[$bank], array(
					'id_option' => $bank . '_' . $months,
					'name' => $months . ' Months'
					));
			}
		}

		$environments = array(
			array(
				'id_option' => 'development',
				'name' => 'Development'
				),
			array(
				'id_option' => 'production',
				'name' => 'Production'
				)
			);

		$installment_type = array(
			array(
				'id_option' => 'off',
				'name' => 'Off'
				),
			array(
				'id_option' => 'all_product',
				'name' => 'All Products'
				),
			array(
				'id_option' => 'certain_product',
				'name' => 'Certain Product'
				)			
			);

		$fields_form = array(
			'form' => array(
				'legend' => array(
					'title' => 'Basic Information',
					'icon' => 'icon-cogs'
					),
				'input' => array(
					array(
						'type' => 'text',
						'label' => 'Payment Button Display Title',
						'name' => 'MT_DISPLAY_TITLE',
						'required' => false,
						'desc' => 'Customize payment button title that will be displayed to your customer when they checkout. e.g: Cicilan CIMB, Cicilan Permata, etc. Leave blank for default title.'
						),
					array(
						'type' => 'select',
						'label' => 'Environment',
						'name' => 'MT_ENVIRONMENT',
						'required' => true,
						'options' => array(
							'query' => $environments,
							'id' => 'id_option',
							'name' => 'name'
							),
						'class' => 'v2_settings sensitive'
						),
					array(
						'type' => 'text',
						'label' => 'Client Key',
						'name' => 'MT_CLIENT_KEY',
						'required' => true,
						'desc' => 'Consult to your Merchant Administration Portal for the value of this field.',
						'class' => 'v1_vtdirect_settings v2_settings sensitive'
						),
					array(
						'type' => 'text',
						'label' => 'Server Key',
						'name' => 'MT_SERVER_KEY',
						'required' => true,
						'desc' => 'Consult to your Merchant Administration Portal for the value of this field.',
						'class' => 'v1_vtdirect_settings v2_settings sensitive'
						),
					array(						
						'type' => (version_compare(Configuration::get('PS_VERSION_DB'), '1.6') == -1)?'radio':'switch',
						'label' => 'Enable 3D Secure?',
						'name' => 'MT_3D_SECURE',
						'required' => true,
						'is_bool' => true,
						'values' => array(
							array(
								'id' => '3d_secure_yes',
								'value' => 1,
								'label' => 'Yes'
								),
							array(
								'id' => '3d_secure_no',
								'value' => 0,
								'label' => 'No'
								)
							),						
						'desc' => 'You must enable 3D Secure. Please contact us if you wish to disable this feature in the Production environment.'
						//'class' => ''
						),
					// array(
					// 	'type' => (version_compare(Configuration::get('PS_VERSION_DB'), '1.6') == -1)?'radio':'switch',
					// 	'label' => 'Enable sanitization',
					// 	'name' => 'MT_SANITIZED',						
					// 	'is_bool' => true,
					// 	'values' => array(
					// 		array(
					// 			'id' => 'sanitized_yes',
					// 			'value' => 1,
					// 			'label' => 'Yes'
					// 			),
					// 		array(
					// 			'id' => 'sanitized_no',
					// 			'value' => 0,
					// 			'label' => 'No'
					// 			)
					// 		),
					// 	//'class' => ''
					// 	),
					array(
						'type' => (version_compare(Configuration::get('PS_VERSION_DB'), '1.6') == -1)?'radio':'switch',
						'label' => 'Credit Card',
						'name' => 'MT_ENABLED_CREDIT_CARD',						
						'is_bool' => true,
						'values' => array(
							array(
								'id' => 'credit_yes',
								'value' => 1,
								'label' => 'Yes'
								),
							array(
								'id' => 'credit_no',
								'value' => 0,
								'label' => 'No'
								)
							),
						//'class' => ''
						),
					array(
						'type' => (version_compare(Configuration::get('PS_VERSION_DB'), '1.6') == -1)?'radio':'switch',
						'label' => 'Bank Transfer',
						'name' => 'MT_ENABLED_PERMATAVA',						
						'is_bool' => true,
						'values' => array(
							array(
								'id' => 'permatava_yes',
								'value' => 1,
								'label' => 'Yes'
								),
							array(
								'id' => 'permatava_no',
								'value' => 0,
								'label' => 'No'
								)
							),
						//'class' => ''
						),
					array(
						'type' => (version_compare(Configuration::get('PS_VERSION_DB'), '1.6') == -1)?'radio':'switch',
						'label' => 'Mandiri Billpayment',
						'name' => 'MT_ENABLED_MANDIRI_BILL',						
						'is_bool' => true,
						'values' => array(
							array(
								'id' => 'mandiri_bill_yes',
								'value' => 1,
								'label' => 'Yes'
								),
							array(
								'id' => 'mandiri_bill_no',
								'value' => 0,
								'label' => 'No'
								)
							),
						//'class' => ''
						),
					array(
						'type' => (version_compare(Configuration::get('PS_VERSION_DB'), '1.6') == -1)?'radio':'switch',
						'label' => 'CIMB Clicks',
						'name' => 'MT_ENABLED_CIMB',						
						'is_bool' => true,
						'values' => array(
							array(
								'id' => 'cimb_yes',
								'value' => 1,
								'label' => 'Yes'
								),
							array(
								'id' => 'cimb_no',
								'value' => 0,
								'label' => 'No'
								)
							),
						//'class' => ''
						),
					array(
						'type' => (version_compare(Configuration::get('PS_VERSION_DB'), '1.6') == -1)?'radio':'switch',
						'label' => 'Mandiri ClickPay',
						'name' => 'MT_ENABLED_MANDIRI',						
						'is_bool' => true,
						'values' => array(
							array(
								'id' => 'mandiri_yes',
								'value' => 1,
								'label' => 'Yes'
								),
							array(
								'id' => 'mandiri_no',
								'value' => 0,
								'label' => 'No'
								)
							),
						//'class' => ''
						),
					array(
						'type' => (version_compare(Configuration::get('PS_VERSION_DB'), '1.6') == -1)?'radio':'switch',
						'label' => 'BRI E-Pay',
						'name' => 'MT_ENABLED_BRIEPAY',						
						'is_bool' => true,
						'values' => array(
							array(
								'id' => 'briepay_yes',
								'value' => 1,
								'label' => 'Yes'
								),
							array(
								'id' => 'briepay_no',
								'value' => 0,
								'label' => 'No'
								)
							),
						//'class' => ''
						),
					array(
						'type' => (version_compare(Configuration::get('PS_VERSION_DB'), '1.6') == -1)?'radio':'switch',
						'label' => 'Telkomsel T-Cash',
						'name' => 'MT_ENABLED_TELKOMSEL_CASH',						
						'is_bool' => true,
						'values' => array(
							array(
								'id' => 'telkomsel_cash_yes',
								'value' => 1,
								'label' => 'Yes'
								),
							array(
								'id' => 'telkomsel_cash_no',
								'value' => 0,
								'label' => 'No'
								)
							),
						//'class' => ''
						),
					array(
						'type' => (version_compare(Configuration::get('PS_VERSION_DB'), '1.6') == -1)?'radio':'switch',
						'label' => 'XL Tunai',
						'name' => 'MT_ENABLED_XL_TUNAI',						
						'is_bool' => true,
						'values' => array(
							array(
								'id' => 'xl_tunai_yes',
								'value' => 1,
								'label' => 'Yes'
								),
							array(
								'id' => 'xl_tunai_no',
								'value' => 0,
								'label' => 'No'
								)
							),
						//'class' => ''
						),
					// array(
					// 	'type' => (version_compare(Configuration::get('PS_VERSION_DB'), '1.6') == -1)?'radio':'switch',
					// 	'label' => 'BBM MONEY',
					// 	'name' => 'MT_ENABLED_BBM_MONEY',						
					// 	'is_bool' => true,
					// 	'values' => array(
					// 		array(
					// 			'id' => 'bbm_money_yes',
					// 			'value' => 1,
					// 			'label' => 'Yes'
					// 			),
					// 		array(
					// 			'id' => 'bbm_money_no',
					// 			'value' => 0,
					// 			'label' => 'No'
					// 			)
					// 		),
					// 	//'class' => ''
					// 	),
					array(
						'type' => (version_compare(Configuration::get('PS_VERSION_DB'), '1.6') == -1)?'radio':'switch',
						'label' => 'Indomaret',
						'name' => 'MT_ENABLED_INDOMARET',						
						'is_bool' => true,
						'values' => array(
							array(
								'id' => 'indomaret_yes',
								'value' => 1,
								'label' => 'Yes'
								),
							array(
								'id' => 'indomaret_no',
								'value' => 0,
								'label' => 'No'
								)
							),
						//'class' => ''
						),
					array(
						'type' => (version_compare(Configuration::get('PS_VERSION_DB'), '1.6') == -1)?'radio':'switch',
						'label' => 'Indosat Dompetku',
						'name' => 'MT_ENABLED_INDOSAT_DOMPETKU',						
						'is_bool' => true,
						'values' => array(
							array(
								'id' => 'indosat_dompetku_yes',
								'value' => 1,
								'label' => 'Yes'
								),
							array(
								'id' => 'indosat_dompetku_no',
								'value' => 0,
								'label' => 'No'
								)
							),
						//'class' => ''
						),
					array(
						'type' => (version_compare(Configuration::get('PS_VERSION_DB'), '1.6') == -1)?'radio':'switch',
						'label' => 'Mandiri Ecash',
						'name' => 'MT_ENABLED_MANDIRI_ECASH',						
						'is_bool' => true,
						'values' => array(
							array(
								'id' => 'mandiri_ecash_yes',
								'value' => 1,
								'label' => 'Yes'
								),
							array(
								'id' => 'mandiri_ecash_no',
								'value' => 0,
								'label' => 'No'
								)
							),
						//'class' => ''
						),
					/*array(
						'type' => 'select',
						'label' => 'Enable Installments',
						'name' => 'MT_ENABLE_INSTALLMENT',						
						'options' => array(
							'query' => $installment_type,
							'id' => 'id_option',
							'name' => 'name'
							),
						//'class' => ''
						),
					array(
						'type' => (version_compare(Configuration::get('PS_VERSION_DB'), '1.6') == -1)?'radio':'switch',
						'label' => 'BNI Installment',
						'name' => 'MT_ENABLED_BNI_INSTALLMENT',						
						'is_bool' => true,
						'values' => array(
							array(
								'id' => 'MT_ENABLED_BNI_INSTALLMENT_on',
								'value' => 1,
								'label' => 'Yes'
								),
							array(
								'id' => 'MT_ENABLED_BNI_INSTALLMENT_off',
								'value' => 0,
								'label' => 'No'
								)
							),
						//'class' => 'MT_ENABLED_BNI_INSTALLMENT'
						),
					array(
						'type' => 'text',
						'label' => 'Enable BNI Installments?',
						'name' => 'MT_INSTALLMENTS_BNI',
						//'class' => 'v1_vtweb_settings sensitive'\
						'class' => 'MT_INSTALLMENTS_BNI'	
						),
					array(
						'type' => (version_compare(Configuration::get('PS_VERSION_DB'), '1.6') == -1)?'radio':'switch',
						'label' => 'MANDIRI Installment',
						'name' => 'MT_ENABLED_MANDIRI_INSTALLMENT',						
						'is_bool' => true,
						'values' => array(
							array(
								'id' => 'MT_ENABLED_MANDIRI_INSTALLMENT_on',
								'value' => 1,
								'label' => 'Yes'
								),
							array(
								'id' => 'MT_ENABLED_MANDIRI_INSTALLMENT_off',
								'value' => 0,
								'label' => 'No'
								)
							),
						//'class' => 'MT_ENABLED_MANDIRI_INSTALLMENT'
						),
					array(
						'type' => 'text',
						'label' => 'Enable Mandiri Installments?',
						'name' => 'MT_INSTALLMENTS_MANDIRI',
						//'class' => 'v1_vtweb_settings sensitive'\
						'class' => 'MT_INSTALLMENTS_MANDIRI'	
						),*/							
				/*	array(
						'type' => 'checkbox',
						'label' => 'Enable Mandiri Installments?',
						'name' => 'MT_INSTALLMENTS',
						'values' => array(
							'query' => $installments_options['MANDIRI'],
							'id' => 'id_option',
							'name' => 'name'
							),
						//'class' => 'v1_vtweb_settings sensitive'
						'class' => 'MT_INSTALLMENTS_MANDIRI'
						),*/
					array(
						'type' => 'select',
						'label' => 'Map payment SUCCESS status to:',
						'name' => 'MT_PAYMENT_SUCCESS_STATUS_MAP',
						'required' => true,	
						'options' => array(
							'query' => $order_states,
							'id' => 'id_option',
							'name' => 'name'
							),
						//'class' => ''
						),
					array(
						'type' => 'select',
						'label' => 'Map payment FAILURE status to:',
						'name' => 'MT_PAYMENT_FAILURE_STATUS_MAP',
						'required' => true,
						'options' => array(
							'query' => $order_states,
							'id' => 'id_option',
							'name' => 'name'
							),
						//'class' => ''
						),
					array(
						'type' => 'select',
						'label' => 'Map payment CHALLENGE status to:',
						'name' => 'MT_PAYMENT_CHALLENGE_STATUS_MAP',
						'required' => true,
						'options' => array(
							'query' => $order_states,
							'id' => 'id_option',
							'name' => 'name'
							),
						//'class' => ''
						),
					array(
						'type' => 'text',
						'label' => 'IDR Conversion Rate',
						'name' => 'MT_KURS',
						'desc' => 'Midtrans will use this rate to convert prices to IDR when there are no default conversion system.'
						),
					),
				'submit' => array(
					'title' => $this->l('Save'),
					)
				)
			);
		// $fields_payment_form = array(
		// 	'form' => array(
		// 		'legend' => array(
		// 			'title' => 'Payment Configuration',
		// 			'icon' => 'icon-cogs'
		// 			),
		// 		'input' => array(					
		// 			array(
		// 				'type' => 'switch',
		// 				'label' => 'CIMB Clicks',
		// 				'name' => 'MT_ENABLED_CIMB',						
		// 				'is_bool' => true,
		// 				'values' => array(
		// 					array(
		// 						'id' => 'cimb_yes',
		// 						'value' => 1,
		// 						'label' => 'Yes'
		// 						),
		// 					array(
		// 						'id' => 'cimb_no',
		// 						'value' => 0,
		// 						'label' => 'No'
		// 						)
		// 					),						
		// 				),
		// 			array(
		// 				'type' => 'switch',
		// 				'label' => 'Mandiri ClickPay',
		// 				'name' => 'MT_ENABLED_MANDIRI',						
		// 				'is_bool' => true,
		// 				'values' => array(
		// 					array(
		// 						'id' => 'mandiri_yes',
		// 						'value' => 1,
		// 						'label' => 'Yes'
		// 						),
		// 					array(
		// 						'id' => 'mandiri_no',
		// 						'value' => 0,
		// 						'label' => 'No'
		// 						)
		// 					),						
		// 				),
		// 			array(
		// 				'type' => 'select',
		// 				'label' => 'Enable Installments',
		// 				'name' => 'MT_ENABLE_INSTALLMENT',						
		// 				'options' => array(
		// 					'query' => $installment_type,
		// 					'id' => 'id_option',
		// 					'name' => 'name'
		// 					),
		// 				),
		// 			array(
		// 				'type' => 'switch',
		// 				'label' => 'BNI Installment',
		// 				'name' => 'MT_ENABLED_BNI_INSTALLMENT',						
		// 				//'is_bool' => true,
		// 				'values' => array(
		// 					array(
		// 						'id' => 'bni_installment_yes',
		// 						'value' => 1,
		// 						'label' => 'Yes'
		// 						),
		// 					array(
		// 						'id' => 'bni_installment_no',
		// 						'value' => 0,
		// 						'label' => 'No'
		// 						)
		// 					),
		// 				),
		// 			array(
		// 				'type' => 'checkbox',
		// 				'label' => 'Enable BNI Installments?',
		// 				'name' => 'MT_INSTALLMENTS',
		// 				'values' => array(
		// 					'query' => $installments_options['BNI'],
		// 					'id' => 'id_option',
		// 					'name' => 'name'
		// 					),
		// 				'class' => 'MT_INSTALLMENTS_BNI'	
		// 				),
		// 			array(
		// 				'type' => 'switch',
		// 				'label' => 'MANDIRI Installment',
		// 				'name' => 'MT_ENABLED_MANDIRI_INSTALLMENT',						
		// 				'is_bool' => true,
		// 				'values' => array(
		// 					array(
		// 						'id' => 'mandiri_installment_yes',
		// 						'value' => 1,
		// 						'label' => 'Yes'
		// 						),
		// 					array(
		// 						'id' => 'mandiri_installment_no',
		// 						'value' => 0,
		// 						'label' => 'No'
		// 						)
		// 					),
		// 				),							
		// 			array(
		// 				'type' => 'checkbox',
		// 				'label' => 'Enable Mandiri Installments?',
		// 				'name' => 'MT_INSTALLMENTS',
		// 				'values' => array(
		// 					'query' => $installments_options['MANDIRI'],
		// 					'id' => 'id_option',
		// 					'name' => 'name'
		// 					),
		// 				'class' => 'MT_INSTALLMENTS_MANDIRI'
		// 				),					
		// 			),
		// 		'submit' => array(
		// 			'title' => $this->l('Save'),
		// 			)
		// 		)
		// 	);

		$helper = new HelperForm();
		$helper->show_toolbar = false;
		$helper->table =  $this->table;
		$lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->default_form_language = $lang->id;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
		$this->fields_form = array();
		$helper->id = (int)Tools::getValue('id_carrier');
		$helper->identifier = $this->identifier;
		$helper->submit_action = 'btnSubmit';
		$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->tpl_vars = array(
			'fields_value' => $this->getConfigFieldsValues(),
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id
		);



		$this->_html .= $helper->generateForm(array($fields_form));
		//$this->_html .= $helper->generateForm(array($fields_payment_form));
	}

	private function _displayFormOld()
	{
		$order_states = array();
		foreach (OrderState::getOrderStates($this->context->cookie->id_lang) as $state) {
			array_push($order_states, array(
				'id_option' => $state['id_order_state'],
				'name' => $state['name']
				)
			);
		}

		$this->context->smarty->assign(array(
			'form_url' => Tools::htmlentitiesUTF8($_SERVER['REQUEST_URI']),
			'api_version' => htmlentities(Configuration::get('MT_API_VERSION'), ENT_COMPAT, 'UTF-8'),
			'api_versions' => array(1 => 'v1', 2 => 'v2'),
			//'payment_type' => htmlentities(Configuration::get('MT_PAYMENT_TYPE'), ENT_COMPAT, 'UTF-8'),
			//'payment_types' => array('vtweb' => 'VT-Web', 'vtdirect' => 'VT-Direct'),
			'client_key' => htmlentities(Configuration::get('MT_CLIENT_KEY'), ENT_COMPAT, 'UTF-8'),
			'server_key' => htmlentities(Configuration::get('MT_SERVER_KEY'), ENT_COMPAT, 'UTF-8'),
			'environments' => array(false => 'Development', true => 'Production'),
			'environment' => htmlentities(Configuration::get('MT_ENVIRONMENT'), ENT_COMPAT, 'UTF-8'),
			'enable_3d_secure' => htmlentities(Configuration::get('MT_3D_SECURE'), ENT_COMPAT, 'UTF-8'),
			'enable_sanitized' => htmlentities(Configuration::get('MT_SANITIZED'), ENT_COMPAT, 'UTF-8'),
			'enabled_cimb' => htmlentities(Configuration::get('MT_ENABLED_CIMB'), ENT_COMPAT, 'UTF-8'),
			'enabled_mandiri' => htmlentities(Configuration::get('MT_ENABLED_MANDIRI'), ENT_COMPAT, 'UTF-8'),
			'enabled_permatava' => htmlentities(Configuration::get('MT_ENABLED_PERMATAVA'), ENT_COMPAT, 'UTF-8'),
			'enabled_indomaret' => htmlentities(Configuration::get('MT_ENABLED_INDOMARET'), ENT_COMPAT, 'UTF-8'),
			'enabled_indosat_dompetku' => htmlentities(Configuration::get('MT_ENABLED_INDOSAT_DOMPETKU'), ENT_COMPAT, 'UTF-8'),
			'enabled_mandiri_ecash' => htmlentities(Configuration::get('MT_ENABLED_MANDIRI_ECASH'), ENT_COMPAT, 'UTF-8'),
			'statuses' => $order_states,
			'payment_success_status_map' => htmlentities(Configuration::get('MT_PAYMENT_SUCCESS_STATUS_MAP'), ENT_COMPAT, 'UTF-8'),
			'payment_challenge_status_map' => htmlentities(Configuration::get('MT_PAYMENT_CHALLENGE_STATUS_MAP'), ENT_COMPAT, 'UTF-8'),
			'payment_failure_status_map' => htmlentities(Configuration::get('MT_PAYMENT_FAILURE_STATUS_MAP'), ENT_COMPAT, 'UTF-8'),
			'kurs' => htmlentities(Configuration::get('MT_KURS', $this->midtrans_kurs), ENT_COMPAT, 'UTF-8'),
			//'kurs' => htmlentities(Configuration::get('VT_INSTALLMENTS_BNI', ENT_COMPAT, 'UTF-8'),
			'convenience_fee' => htmlentities(Configuration::get('MT_CONVENIENCE_FEE', $this->midtrans_convenience_fee), ENT_COMPAT, 'UTF-8'),
			'this_path' => $this->_path,
			'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/'
			));
		$output = $this->context->smarty->fetch(__DIR__ . '/views/templates/hook/admin_retro.tpl');
		$this->_html .= $output;
	}

	public function getContent()
	{
		// $this->_html = '<h2>'.$this->displayName.'</h2>';

		if (Tools::isSubmit('btnSubmit'))
		{
			$this->_postValidation();
			if (!count($this->_postErrors))
				$this->_postProcess();
			else
				foreach ($this->_postErrors as $err)
					$this->_html .= '<div class="alert alert-danger error">'. $err . '</div>';
		}
		else
			$this->_html .= '<br />';

		$this->_displayMidtransPay();
		$this->_displayForm();

		return $this->_html;
	}

	public function hookDisplayHeader($params)
	{
		
	}

	// working in 1.5 and 1.6
	public function hookDisplayBackOfficeHeader($params)
	{
		$this->context->controller->addJquery();
		$this->context->controller->addJS($this->_path . 'js/midtrans_admin.js', 'all');
	}

	public function hookPayment($params)
	{
		return $this->hookDisplayPayment($params);				
	}

	public function hookDisplayPayment($params)
	{
		if (!$this->active)
			return;

		if (!$this->checkCurrency($params['cart']))
			return;

		$cart = $this->context->cart;

		$display_title = strlen(Configuration::get('MT_DISPLAY_TITLE')) > 0 ? Configuration::get('MT_DISPLAY_TITLE') : "Online Payment via Midtrans" ;

		$this->context->smarty->assign(array(
			'cart' => $cart,
			'mt_displayname' => $display_title,
			'this_path' => $this->_path,
			'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/'
		));

		// 1.4 compatibility
		if (version_compare(Configuration::get('PS_VERSION_DB'), '1.5') == -1) {
			return $this->display(__FILE__, 'views/templates/hook/payment.tpl');
		} else
		{
			return $this->display(__FILE__, 'payment.tpl');	
		}
	}

	public function hookOrderConfirmation($params)
	{
		if (!$this->active)
			return;

		if (!$this->checkCurrency($params['cart']))
			return;

		$order = new Order(Tools::getValue('id_order'));
		$history = $order->getHistory($this->context->cookie->id_lang);	

		$history = $history[0];

		$this->context->smarty->assign(array(
			'transaction_status' => $history['id_order_state'],
			'cart' => $this->context->cart,
			'this_path' => $this->_path,
			'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/'
		));

		if (version_compare(Configuration::get('PS_VERSION_DB'), '1.5') == -1) {
			return $this->display(__FILE__, 'views/templates/hook/order_confirmation.tpl');
		} else
		{
			return $this->display(__FILE__, 'order_confirmation.tpl');	
		}
	}
	
	public function checkCurrency($cart)
	{
		$currency_order = new Currency($cart->id_currency);
		$currencies_module = $this->getCurrency($cart->id_currency);

		if (is_array($currencies_module))
			foreach ($currencies_module as $currency_module)
				if ($currency_order->id == $currency_module['id_currency'])
					return true;
		return false;
	}

	// Retrocompatibility 1.4/1.5
	private function initContext()
	{
	  if (class_exists('Context'))
	    $this->context = Context::getContext();
	  else
	  {
	    global $smarty, $cookie;
	    $this->context = new StdClass();
	    $this->context->smarty = $smarty;
	    $this->context->cookie = $cookie;
	    if (array_key_exists('cart', $GLOBALS))
	    {
	    	global $cart;
	    	$this->context->cart = $cart;
	    }
	    if (array_key_exists('link', $GLOBALS))
	    {
	    	global $link;
	    	$this->context->link = $link;
	    }
	  }
	}

	// Retrocompatibility 1.4
	public function execPayment($cart)
	{
		if (!$this->active)
			return ;
		if (!$this->checkCurrency($cart))
			Tools::redirectLink(__PS_BASE_URI__.'order.php');

		$link = new Link();

		global $cookie, $smarty;

		$smarty->assign(array(
			'payment_type' => Configuration::get('MT_PAYMENT_TYPE'),
			'api_version' => Configuration::get('MT_API_VERSION'),
			'error_message' => '',
			'link' => $link,
			'nbProducts' => $cart->nbProducts(),
			'cust_currency' => $cart->id_currency,
			'currencies' => $this->getCurrency((int)$cart->id_currency),
			'total' => $cart->getOrderTotal(true, Cart::BOTH),
			'this_path' => $this->_path,
			'this_path_ssl' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.((int)Configuration::get('PS_REWRITING_SETTINGS') && count(Language::getLanguages()) > 1 && isset($smarty->ps_language) && !empty($smarty->ps_language) ? $smarty->ps_language->iso_code.'/' : '').'modules/'.$this->name.'/'
		));

		return $this->display(__FILE__, 'views/templates/front/payment_execution.tpl');
	}
//
	public function getTermInstallment($name_bank){
		$ans = array();
		foreach ($this->config_keys as $key) {
			if ( (strpos($key, 'MT_INSTALLMENTS_' . $name_bank ) !== FALSE) && (Configuration::get($key) == 'on') ){
				
				$term = Configuration::get('MT_INSTALLMENTS_'.$name_bank);
				
				$key_array = explode('_', $key);
				//error_log($key); // debug
				//error_log(print_r($key_array,true)); // debug
				$ans[] = $key_array[3];
				//error_log($key_array[3]); // debug
			}
    		
		}
		//return $ans;
		return $term2;
	}

	public function isInstallmentCart($products){		
		foreach($products as $prod){
			$str_attr = $prod['attributes_small'];
			if (strpos(strtolower($str_attr), 'installment') !== FALSE){				
				return true;
			}    
		}		
		return false;
	}

	// Retrocompatibility 1.4
	public function execValidation($cart)
	{
		global $cookie;

		if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->active)
			Tools::redirect('index.php?controller=order&step=1');

		// Check that this payment option is still available in case the customer changed his address just before the end of the checkout process
		$authorized = false;
		foreach (Module::getPaymentModules() as $module)
			if ($module['name'] == 'midtranspay')
			{
				$authorized = true;
				break;
			}
		if (!$authorized)
			die($this->module->l('This payment method is not available.', 'validation'));

		$customer = new Customer($cart->id_customer);
		if (!Validate::isLoadedObject($customer))
			Tools::redirect('index.php?controller=order&step=1');

		$usd = Configuration::get('MT_KURS');
		$cf = Configuration::get('MT_CONVENIENCE_FEE') * 0.01;

		$list_enable_payments = array();
		
		if (Configuration::get('MT_ENABLED_CREDIT_CARD')){
			$list_enable_payments[] = "credit_card";
		}		
		if (Configuration::get('MT_ENABLED_CIMB')){
			$list_enable_payments[] = "cimb_clicks";
		}
		if (Configuration::get('MT_ENABLED_MANDIRI')){
			$list_enable_payments[] = "mandiri_clickpay";
		}
		if (Configuration::get('MT_ENABLED_PERMATAVA')){
			$list_enable_payments[] = "bank_transfer";
		}
		if (Configuration::get('MT_ENABLED_BRIEPAY')){
			$list_enable_payments[] = "bri_epay";
		}
		if (Configuration::get('MT_ENABLED_TELKOMSEL_CASH')){
			$list_enable_payments[] = "telkomsel_cash";
		}
		if (Configuration::get('MT_ENABLED_XL_TUNAI')){
			$list_enable_payments[] = "xl_tunai";
		}	
		if (Configuration::get('MT_ENABLED_MANDIRI_BILL')){
			$list_enable_payments[] = "echannel";
		}
		if (Configuration::get('MT_ENABLED_BBM_MONEY')){
			$list_enable_payments[] = "bbm_money";
		}
		if (Configuration::get('MT_ENABLED_INDOMARET')){
			$list_enable_payments[] = "cstore";
		}
		if (Configuration::get('MT_ENABLED_INDOSAT_DOMPETKU')){
			$list_enable_payments[] = "indosat_dompetku";
		}
		if (Configuration::get('MT_ENABLED_MANDIRI_ECASH')){
			$list_enable_payments[] = "mandiri_ecash";
		}
		//error_log(print_r($list_enable_payments,TRUE));	

		$veritrans = new Veritrans_Config();
		//SETUP
		Veritrans_Config::$serverKey = Configuration::get('MT_SERVER_KEY');
		Veritrans_Config::$isProduction = Configuration::get('MT_ENVIRONMENT') == 'production' ? true : false;

		$url = Veritrans_Config::getBaseUrl(); 

		if (version_compare(Configuration::get('PS_VERSION_DB'), '1.5') == -1)
		{
			$shipping_cost = $cart->getOrderShippingCost();
		} else
		{
			$shipping_cost = $cart->getTotalShippingCost();
		}

		$currency = new Currency($cookie->id_currency);
		$total = $cart->getOrderTotal(true, Cart::BOTH);
		
		$mailVars = array(
		 );
				
		$billing_address = new Address($cart->id_address_invoice);
		$delivery_address = new Address($cart->id_address_delivery);
		
		
    	if (Configuration::get('MT_3D_SECURE') == 'on' || Configuration::get('MT_3D_SECURE') == 1)
			Veritrans_Config::$is3ds = true;		

		// if (Configuration::get('MT_SANITIZED') == 'on' || Configuration::get('MT_SANITIZED') == 1)
			Veritrans_Config::$isSanitized = true;

		// Billing Address
    	$params_billing_address = array(
    			'first_name' => $billing_address->firstname, 
				'last_name' => $billing_address->lastname, 
				'address' => $billing_address->address1,
				'city' => $billing_address->city, 
				'postal_code' => $billing_address->postcode, 
				'phone' => $this->determineValidPhone($billing_address->phone, $billing_address->phone_mobile), 
				'country_code' => 'IDN'
    		);

		if($cart->isVirtualCart()) {
			
		} else {
			if ($cart->id_address_delivery != $cart->id_address_invoice)
			{
				$params_shipping_address = array(
					'first_name' => $delivery_address->firstname, 
					'last_name' => $delivery_address->lastname, 
					'address' => $delivery_address->address1,
					'city' => $delivery_address->city,
					'postal_code' => $delivery_address->postcode,
					'phone' => $this->determineValidPhone($delivery_address->phone, $delivery_address->phone_mobile), 
					'country_code' => 'IDN'
					);																								
			} else
			{
				$params_shipping_address = $params_billing_address;
			}
		}  
    	
		$params_customer_details = array(
			'first_name' => $billing_address->firstname, 
			'last_name' =>  $billing_address->lastname, 
			'email' => $customer->email, 
			'phone' => $this->determineValidPhone($billing_address->phone, $billing_address->phone_mobile), 
			'billing_address' => $params_billing_address, 
			'shipping_address' => $params_shipping_address
			);

		$items = $this->addCommodities($cart, $shipping_cost, $usd);				
		
		// convert the currency
		$cart_currency = new Currency($cart->id_currency);
		if ($cart_currency->iso_code != 'IDR')
		{
			// check whether if the IDR is installed or not
			if (Currency::exists('IDR', null))
			{
			  // use default rate
			  if (version_compare(Configuration::get('PS_VERSION_DB'), '1.5') == -1)
			  {
				$conversion_func = function($input) use($cart_currency) { return Tools::convertPrice($input, new Currency(Currency::getIdByIsoCode('IDR')), true); };
			  } else
			  {
				$conversion_func = function($input) use($cart_currency) { return Tools::convertPriceFull($input, $cart_currency, new Currency(Currency::getIdByIsoCode('IDR'))); };
			  }
			} else
			{
			  // use rate
			  $conversion_func = function($input) { return $input * intval(Configuration::get('MT_KURS')); };
			}
			foreach ($items as &$item) {						
				$item['price'] = intval(ceil(call_user_func($conversion_func, $item['price'])));				
			}
		}else if($cart_currency->iso_code == 'IDR')
		{
			foreach ($items as &$item) {						
				$item['price'] = intval(ceil($item['price']));				
			}
		}
				

		$this->validateOrder($cart->id, Configuration::get('MT_ORDER_STATE_ID'), $cart->getOrderTotal(true, Cart::BOTH), $this->displayName, NULL, $mailVars, (int)$currency->id, false, $customer->secure_key);				
		
		$gross_amount = 0;
		unset($item);
		foreach ($items as $item) {				
			$gross_amount += $item['price'] * $item['quantity'];
		}	
		
		$isBniInstallment = Configuration::get('MT_ENABLED_BNI_INSTALLMENT') == 1;
		$isMandiriInstallment = Configuration::get('MT_ENABLED_MANDIRI_INSTALLMENT') == 1;
		$warning_redirect = false;
		$fullPayment = true;

		// $installment_type_val = Configuration::get('MT_ENABLE_INSTALLMENT');
		$installment_type_val = "off";
		$param_required;
		switch ($installment_type_val) {
			case 'all_product':
								
				if ($isBniInstallment){					
					//$bni_term2 = $this->getTermInstallment('BNI');
					$a = Configuration::get('MT_INSTALLMENTS_BNI');
					$term = explode(',',$a);
					$bni_term = $term;
					//error_log(print_r($bni_term,true));
					//error_log($bni_term,true);
				}					
							
				if ($isMandiriInstallment){

					$mandiri_term =	$this->getTermInstallment('MANDIRI');
					
					$a = Configuration::get('MT_INSTALLMENTS_MANDIRI');
					$term = explode(',',$a);
					$mandiri_term = $term;

					//error_log($mandiri_term,true);
					//error_log(print_r($mandiri_term,true));
				}				
				
				$param_installment = array();
				if ($isBniInstallment){
					$param_installment['bni'] = $bni_term;
				}

				if ($isMandiriInstallment){
					$param_installment['mandiri'] = $mandiri_term;
				}
				$param_required = "false";
				$fullPayment = false;
				break;
			case 'certain_product':
				$param_installment = null;
				$products_cart = $cart->getProducts();
				$num_product = count($products_cart);
				if($num_product == 1){
					$attr_product = explode(',',$products_cart[0]['attributes_small']);
					foreach($attr_product as $att){
						$att_trim = ltrim($att);						
						$att_arr = explode(' ',$att_trim);
						//error_log(print_r($att_arr,true));
						if(strtolower($att_arr[0]) == 'installment'){
							$fullPayment = false;
							$param_installment = array();
							$param_installment[strtolower($att_arr[1])] = array($att_arr[2]);
						} 						
					}
				} else {
					$warning_redirect = true;
					$keys['message'] = 1;
				}
				$param_required = "true";				
				break;						
			case 'off':
				$param_installment = null;
				break;
		}		
	

	//error_log($param_installment,true);
		// $param_payment_option = array(
		// 	'installment' => array(
		// 						'required' => $param_required,
		// 						'installment_terms' => $param_installment 
		// 					)
		// 	);

		$params_all = array(
			'enabled_payments' => $list_enable_payments,
			'transaction_details' => array(
				'order_id' => $this->currentOrder, 
				'gross_amount' => $gross_amount
				),
			'item_details' => $items,
			'customer_details' => $params_customer_details
			);
		
		// if ($gross_amount < 500000){
		// 	$warning_redirect = true;
		// 	$keys['message'] = 2;
		// }

		if( !$warning_redirect && 
			($isBniInstallment || $isMandiriInstallment) && 
			(!$fullPayment)  ){

			$params_all['vtweb']['payment_options'] = $param_payment_option;		
		}

		if (Configuration::get('MT_API_VERSION') == 2 && Configuration::get('MT_PAYMENT_TYPE') != 'vtdirect') 
		{						
			try {
			    // Redirect to Veritrans VTWeb page
			    if ($this->isInstallmentCart($cart->getProducts()) || ($installment_type_val == 'all_product')){
			    	$keys['isWarning'] = $warning_redirect;
			    } else {
			    	$keys['isWarning'] = false;
			    }
			    // error_log(print_r($params_all,true)); // debug
			  	// $keys['redirect_url'] = Veritrans_Vtweb::getRedirectionUrl($params_all);
			  	
			  	$snapToken = Veritrans_Snap::getSnapToken($params_all);
			  	$redirect_url= $this->context->link->getModuleLink($this->name,'snappay',['snap_token' => $snapToken]);
			  	// error_log("redirect_url :".$redirect_url); // debug
			  	$keys['redirect_url'] = $redirect_url;
			}
			catch (Exception $e) {
			  	$keys['errors'] = $e->getMessage();
			  	echo $e->getMessage();
			}
			return $keys;
			
		} else
		if (Configuration::get('MT_API_VERSION') == 2 && Configuration::get('MT_PAYMENT_TYPE') == 'vtdirect')
		{
			echo 'not yet implementation.';
			exit;
		} else
		{
			echo 'The Veritrans API versions and the payment type is not valid.';
			exit;
		}
	}

	public function setMedia()
	{
		Tools::addJs('function onloadEvent() { document.form_auto_post.submit(); }');
	}

	public function addCommodities($cart, $shipping_cost, $usd)
	{
		
		$products = $cart->getProducts();
		$discount = -1 * $cart->getOrderTotal(true, Cart::ONLY_DISCOUNTS);

		if (version_compare(Configuration::get('PS_VERSION_DB'), '1.5') == -1){ // for 1.4 version, voucher is negative
			$discount *= -1;
		}

		$commodities = array();
		$price = 0;

		foreach ($products as $aProduct) {
			//error_log('detail product'); // debug
			//error_log(print_r($aProduct,true)); // debug
			$commodities[] = array(
				"id" => $aProduct['id_product'],
				"price" =>  $aProduct['price_wt'],
				"quantity" => $aProduct['cart_quantity'],
				"name" => $aProduct['name']				
			);
		}

		if($shipping_cost != 0){
			$commodities[] = array(
				"id" => 'SHIPPING_FEE',
				"price" => $shipping_cost, // defer currency conversion until the very last time
				"quantity" => '1',
				"name" => 'Shipping Cost',				
			);			
		}
		
		if($discount != 0){
			$commodities[] = array(
				"id" => 'DISCOUNT_VOUCHER',
				"price" => $discount, // defer currency conversion until the very last time
				"quantity" => '1',
				"name" => 'discount from voucher',				
			);	
		}
		//error_log(print_r($commodities,true)); // debug
		return $commodities;
	}

	function insertTransaction($customer_id, $id_cart, $id_currency, $request_id, $token_merchant)
	{
		$sql = 'INSERT INTO `'._DB_PREFIX_.'mt_transaction`
				(`id_customer`, `id_cart`, `id_currency`, `request_id`, `token_merchant`)
				VALUES ('.(int)$customer_id.',
					'.(int)$id_cart.',
					'.(int)$id_currency.',
						\''.$request_id.'\',
						\''.$token_merchant.'\')';
		Db::getInstance()->Execute($sql);
	}

	function getTransaction($request_id)
	{
		$sql = 'SELECT *
			FROM `'._DB_PREFIX_.'mt_transaction`
			WHERE `request_id` = \''.$request_id.'\'';
		$result = Db::getInstance()->getRow($sql);
		return $result; 
	}

	// determine the phone number to make Midtrans happy
	function determineValidPhone($home_phone = '', $mobile_phone = '')
	{
		if (empty($home_phone) && !empty($mobile_phone))
		{
			return $mobile_phone;
		} else if (!empty($home_phone) && empty($mobile_phone))
		{
			return $home_phone;
		} else if (!empty($home_phone) && !empty($mobile_phone))
		{
			return $mobile_phone;
		} else
		{
			return '081111111111';
		}
	}

	
	public function execNotification()
	{
		$veritrans = new Veritrans_Config();
		
		Veritrans_Config::$isProduction = Configuration::get('MT_ENVIRONMENT') == 'production' ? true : false;
		Veritrans_Config::$serverKey = Configuration::get('MT_SERVER_KEY');

		$midtrans_notification = new Veritrans_Notification();
		$history = new OrderHistory();
		$history->id_order = (int)$midtrans_notification->order_id;

		// error_log('message notif'); // debug
		// error_log(print_r($midtrans_notification,TRUE)); // debug
		// error_log('=============================================='); // debug
		
		// check if order history already been updated to payment success, then save to array $order_history.
		$order_id_notif = (int)$midtrans_notification->order_id;
		$order = new Order($order_id_notif);
		$order_histories = $order->getHistory($this->context->language->id, Configuration::get('MT_PAYMENT_SUCCESS_STATUS_MAP') );
		// if (empty($order_histories))  // debug
		// 	error_log("not found in DB");  // debug
		// error_log(print_r($order_histories,true));  // debug
		// print_r($order_histories,true);  // debug

		//Validating order
		//if ($midtrans_notification->isVerified())
		//{
		  	//$history->id_order = (int)$midtrans_notification->order_id;		  	
			//error_log('notif verified'); // debug
			//error_log('message notif: '.(int)$midtrans_notification->order_id); // debug
			if ($midtrans_notification->transaction_status == 'capture')				
		    {
		     	if ($midtrans_notification->fraud_status== 'accept')
		     	{
		     		// if order history !contains payment accepted, then update DB. Else, don't update DB
		     		if (empty($order_histories))
		     		{
			       		$history->changeIdOrderState(Configuration::get('MT_PAYMENT_SUCCESS_STATUS_MAP'), $order_id_notif);
			       		echo 'Valid success notification accepted.';
		       		}
		       		else{
		       			error_log("########## Transaction has already been updated to success status once, no need to update again"); // debug
		       		}
		       	}
		       	else if ($midtrans_notification->fraud_status== 'challenge')
		     	{
		       		$history->changeIdOrderState(Configuration::get('MT_PAYMENT_CHALLENGE_STATUS_MAP'), $order_id_notif);
		       		echo 'Valid challenge notification accepted.';
		     	} 		       	
		     } else if ($midtrans_notification->transaction_status == 'settlement'){

		     	if($midtrans_notification->payment_type != 'credit_card')
		     	{
		     		// if order history !contains payment accepted, then update DB. Else, don't update DB
		     		if (empty($order_histories)){
				     	$history->changeIdOrderState(Configuration::get('MT_PAYMENT_SUCCESS_STATUS_MAP'), $order_id_notif);
				       	echo 'Valid success notification accepted.';
				    }else{
		       			error_log("########## Transaction has already been updated to success status once, no need to update again"); // debug
		       		}
		     	}
		     	else
		     	{
		     		echo 'Credit card settlement notification accepted.';	
		     	}

		     }else if ($midtrans_notification->transaction_status == 'pending'){
		     	$history->changeIdOrderState(Configuration::get('MT_PAYMENT_CHALLENGE_STATUS_MAP'), $order_id_notif);
		       	echo 'Pending notification accepted.';
		     }else if ($midtrans_notification->transaction_status == 'cancel'){
		     	$history->changeIdOrderState(Configuration::get('MT_PAYMENT_FAILURE_STATUS_MAP'), $order_id_notif);
		       	echo 'Pending notification accepted.';
		     }else if ($midtrans_notification->transaction_status == 'expire'){
		     	$history->changeIdOrderState(Configuration::get('MT_PAYMENT_FAILURE_STATUS_MAP'), $order_id_notif);
		       	echo 'Expire notification accepted.';
		     }
			 else
		     {
		       $history->changeIdOrderState(Configuration::get('MT_PAYMENT_FAILURE_STATUS_MAP'), $order_id_notif);
		       echo 'Valid failure notification accepted';
		     }

		    try{
		    	$history->add(true);		     			  
		    } catch(Exception $e) {
				echo 'Order history not added: ' .$e->getMessage();
				exit;
			}
		//}
		exit;
	}
}
