<?php 

$useSSL = true;

$root_dir = str_replace('modules/midtranspay', '', dirname($_SERVER['SCRIPT_FILENAME']));

include_once($root_dir.'/config/config.inc.php');

$controller = new FrontController();

if (Tools::usingSecureMode())
  $useSSL = $controller->ssl = true;

$controller->init();

include_once($root_dir.'/modules/midtranspay/midtranspay.php');

if (!$cookie->isLogged(true))
  Tools::redirect('authentication.php?back=order.php');
elseif (!Customer::getAddressesTotalById((int)($cookie->id_customer)))
  Tools::redirect('address.php?back=order.php?step=1');

$midtransPay = new MidtransPay();
$keys = $midtransPay->execValidation($cart);

if ($keys['errors'])
{
	var_dump($keys['errors']);
	exit;
} else
{
}