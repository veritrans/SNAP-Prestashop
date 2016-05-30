<?php 

// require_once(dirname(__FILE__).'/../../library/veritrans/Veritrans/Notification.php');


class MidtransPayNotificationModuleFrontController extends ModuleFrontController
{
  /**
   * @see FrontController::postProcess()
   */
  public function postProcess()
  { 
    // we don't use shopping cart again
    // $cart = $this->context->cart;
    // var_dump($cart);

    // $midtrans_notification = new VeritransNotification();
    // $transaction = $this->getTransaction($midtrans_notification->orderId);
    // $order_id = $midtrans_notification->orderId;

    // $token_merchant = $transaction['token_merchant'];
    // $customer = new Customer($transaction['id_customer']); 
    // error_log($transaction);
    // error_log($customer);

    // $cart = new Cart($transaction['id_cart']); 
    // var_dump($cart);
    
    // $currency = new Currency($transaction['id_currency']); 
    // var_dump($currency);

    // $total = (float)$cart->getOrderTotal(true, Cart::BOTH); 
    // var_dump($total);
    
    // $mailVars = array(
    //   '{merchant_id}' => Configuration::get('MERCHANT_ID'),
    //   '{merchant_hash}' => nl2br(Configuration::get('MERCHANT_HASH'))
    // );

    
    /** Validating order*/
    // if ($midtrans_notification->status != 'fatal')
    // {
    //   if($token_merchant == $midtrans_notification->TOKEN_MERCHANT)
    //   {
    //     $history = new OrderHistory();
    //     $history->id_order = (int)$midtrans_notification->orderId; 
    //     if ($midtrans_notification->mStatus == 'success')
    //     { 
    //       // $this->module->validateOrder($cart->id, Configuration::get('VT_PAYMENT_SUCCESS_STATUS_MAP'), $total, $this->module->displayName, NULL, $mailVars, (int)$currency->id, false, $customer->secure_key);     
    //       $history->changeIdOrderState(Configuration::get('VT_PAYMENT_SUCCESS_STATUS_MAP'), (int)$midtrans_notification->orderId);
    //       // $status = "Payment Success";
    //       // $this->validate($this->module->currentOrder, $midtrans_notification->orderId, $status);
    //       echo 'validation success';
      
    //     }
    //     elseif ($midtrans_notification->mStatus == 'failure')
    //     {
    //       // $this->module->validateOrder($cart->id, Configuration::get('VT_PAYMENT_FAILURE_STATUS_MAP'), $total, $this->module->displayName, NULL, $mailVars, (int)$currency->id, false, $customer->secure_key);
    //       $history->changeIdOrderState(Configuration::get('VT_PAYMENT_SUCCESS_FAILURE_MAP'), (int)$midtrans_notification->orderId);
    //       // $status = "Payment Error";
    //       // $this->validate($this->module->currentOrder, $midtrans_notification->orderId, $status);
    //       echo 'validation failed';
    //     }
    //     else
    //     {
    //       echo 'other<br/>';
    //     }     
    //   }
    //   else
    //   {
    //     echo 'no transaction<br/>';
    //   }
    // }
    // exit;
    
    $midtransPay = new MidtransPay();
    $midtransPay->execNotification();
    
  }

  function getTransaction($request_id)
  {
    $sql = 'SELECT *
        FROM `'._DB_PREFIX_.'mt_transaction`
        WHERE `request_id` = \''.$request_id.'\'';
    $result = Db::getInstance()->getRow($sql);
    return $result; 
  }

  function validate($id_transaction, $id_order, $order_status)
  {
    $sql = 'INSERT INTO `'._DB_PREFIX_.'mt_validation`
        (`id_order`, `id_transaction`, `order_status`)
        VALUES ('.(int)$id_transaction.',
            '.(int)$id_order.',
            \''.$order_status.'\')';
    Db::getInstance()->Execute($sql);
  }
}