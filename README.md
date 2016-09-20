
Veritrans&nbsp; Prestashop Payment Gateway Module
=====================================


Veritrans&nbsp;  :heart: Prestashop!
Let your Prestashop store integrated with Veritrans&nbsp;  payment gateway.

### Description

Veritrans&nbsp;  payment gateway is an online payment gateway. They strive to make payments simple for both the merchant and customers. With this plugin you can allow online payment on your Prestashop store using Veritrans&nbsp;  payment gateway.

Payment Method Feature:
- Veritrans&nbsp; Snap all payment method fullpayment
- Credit card online & offline installment payment
- Credit card BIN & bank transfer promo payment
- Credit card MIGS acquiring channel

### Installation Instruction

#### Minimum Requirements

* Prestashop 1.6 or greater
* PHP version 5.4 or greater
* MySQL version 5.0 or greater

#### Installation & Configuration

1. [Download](/archive/master.zip) the modules from this repository.
2. Extract the modules, then rename the folder modules as **midtranspay** and zip this modules as **midtranspay.zip**
3. Go to your Prestashop administration page and go to **"Modules and Services > Modules and Services"** menu.
4. Click on the **"Add a new module"** and locate the **midtranspay.zip** file, then upload it.
5. Find the **Midtrans Pay** module in the module list and click **install**, then enable it.
6. Find the **Midtrans Pay** module in the module list and click **configure**
  * Fill **Payment Button Display Title** with text button that you want to display to customer
  * Select **Environment**, Sandbox is for testing transaction, Production is for real transaction
  * Fill in the **client key** & **server key** with your corresonding [Veritrans&nbsp;  account](https://my.veritrans.co.id/) credentials
  * Note: key for Sandbox & Production is different, make sure you use the correct one.
  * **Map payment SUCCESS status to:** select your desired order status when payment is success.
  * **Map payment FAILURE status to:** select your desired order status when payment is failure.
  * **Map payment PENDING/CHALLENGE status to:** select your desired order status when payment is challanged.
  * Other configuration are optional, you may leave it as is.


### Veritrans&nbsp;  MAP Configuration

1. Login to your [Veritrans&nbsp;  Account](https://my.veritrans.co.id), select your environment (sandbox/production), go to menu `settings > configuration`
   * Payment Notification URL: `http://[your-site-url]/index.php?fc=module&module=midtranspay&controller=notification`
   * Finish Redirect URL: `http://[your-site-url]/index.php?fc=module&module=midtranspay&controller=success`
   * Unfinish Redirect URL: `http://[your-site-url]/index.php?fc=module&module=midtranspay&controller=back`
   * Error Redirect URL: `http://[your-site-url]/index.php?fc=module&module=midtranspay&controller=failure`

#### Get help

* [SNAP-Prestashop Wiki](https://github.com/veritrans/SNAP-Prestashop/wiki)
* [Veritrans registration](https://my.veritrans.co.id/register)
* [SNAP documentation](http://snap-docs.veritrans.co.id)
* Can't find answer you looking for? email to [support@veritrans.co.id](mailto:support@veritrans.co.id)
