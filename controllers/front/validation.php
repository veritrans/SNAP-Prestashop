<?php

class MidtransPayValidationModuleFrontController extends ModuleFrontController
{
  public $display_header = false;
  public $display_footer = false;
  public $display_column_left = false;
  public $display_column_right = false;

  /**
   * @see FrontController::postProcess()
   */
  public function postProcess()
  { 
    $cart = $this->context->cart;
    $midtranspay = new MidtransPay();
    $keys = $midtranspay->execValidation($cart);

    $midtrans_api_version = Configuration::get('MT_API_VERSION');
    $midtrans_payment_method = Configuration::get('MT_PAYMENT_TYPE');

    if (array_key_exists('errors', $keys)){
      if ($keys['errors'])
      {
        var_dump($keys['errors']);
        exit;
      }
    }
    if ($midtrans_api_version == 2 && $midtrans_payment_method == 'vtweb')
    {
        if ($keys['isWarning']){          

            Tools::redirectLink('index.php?fc=module&module=midtranspay&controller=warning&redirlink='.$keys['redirect_url'].'&message='.$keys['message']);
        }      
        Tools::redirectLink($keys['redirect_url']);
    } else if ($midtrans_api_version == 2 && $midtrans_payment_method == 'vtdirect')
    {

    }
  }

  public function setMedia()
  {
    Tools::addJs('function onloadEvent() { document.form_auto_post.submit(); }');
  }

}

