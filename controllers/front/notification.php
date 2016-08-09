<?php 

// require_once(dirname(__FILE__).'/../../library/veritrans/Veritrans/Notification.php');


class MidtransPayNotificationModuleFrontController extends ModuleFrontController
{
  /**
   * @see FrontController::postProcess()
   */
  public function postProcess()
  { 
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