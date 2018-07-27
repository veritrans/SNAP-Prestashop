<?php

class MidtransPayValidation17ModuleFrontController extends ModuleFrontController
{
    /**
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {

        $cart = $this->context->cart;
        if ($cart->id_customer == 0 || !Validate::isLoadedObject($customer = new Customer($cart->id_customer)) || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active) {
            Tools::redirect('index.php?controller=order&step=1');
        }
        // Check that this payment option is still available in case the customer changed his address just before the end of the checkout process
        $authorized = false;
        foreach (Module::getPaymentModules() as $module) {
            if ($module['name'] == 'midtranspay') { //TODO change to dynamic
                $authorized = true;
                break;
            }
        }
        if (!$authorized) {
            die($this->module->l('This payment method is not available.', 'validation'));
        }

        $midtranspay = new MidtransPay();
        $keys = $midtranspay->execValidation($cart);
        // if ($keys['isWarning']){          
        //     Tools::redirectLink('index.php?fc=module&module=midtranspay&controller=warning&redirlink='.$keys['redirect_url'].'&message='.$keys['message']); }
        Tools::redirectLink($keys['redirect_url']);
    }
}