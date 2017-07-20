
Midtrans&nbsp; Prestashop Payment Gateway Module
=====================================


Midtrans&nbsp; :heart: Prestashop!
Integrate your Prestashop store with Midtrans&nbsp; Snap payment gateway.

### Description

Midtrans&nbsp; Snap is an online payment gateway. They strive to make payments simple for both the merchant and customers. This plugin will allow online payment on your Prestashop store using various online payment channel.

Payment Method Feature:
- Midtrans&nbsp; Snap all payment method fullpayment
- Online & offline installment payment
- BIN, bank transfer, and other channel promo payment
- MIGS acquiring channel
- Custom expiry
- Custom fields
- Two-click & One-click feature

### Installation Instruction

#### Minimum Requirements

* Prestashop 1.6 & 1.7 or greater
* PHP version 5.4 or greater
* MySQL version 5.0 or greater

#### Installation & Configuration
1. [Download](../../archive/prestashop1.7.zip) the modules from this repository.
2. Extract the modules, then rename the folder modules as **midtranspay** and zip this modules as **midtranspay.zip**
3. Go to your Prestashop administration page and go to **"Modules and Services > Modules and Services"** menu.
4. Click on the **"Add a new module"** or **"Add a new module"** and locate the **midtranspay.zip** file, then upload it.
5. Find the **Midtrans Pay** module in the module list and click **install**, then enable it.
6. Find the **Midtrans Pay** module in the installed modules list and click **configure**
   * Fill **Payment Button Display Title** with text button that you want to display to customer
   * Select **Environment**, Sandbox is for testing transaction, Production is for real transaction
   * Fill in the **client key** & **server key** with your corresonding [Midtrans&nbsp;  account](https://dashboard.midtrans.com/) credentials
   * Note: key for Sandbox & Production is different, make sure you use the correct one.
   * **Map payment SUCCESS status to:** select your desired order status when payment is success (recommended: Payment accepted).
   * **Map payment FAILURE status to:** select your desired order status when payment is failure (recommended: Payment error).
   * **Map payment PENDING/CHALLENGE status to:** select your desired order status when payment is challanged (recommended: Awaiting Midtrans payment).
   * Other configuration are optional, you can leave it as default.


### Midtrans&nbsp;  MAP Configuration
1. Login to your [Midtrans&nbsp;  Account](https://dashboard.midtrans.com), select your environment (sandbox/production), go to menu `settings > configuration`
   * Payment Notification URL: 
    >`http://[your-site-url]/index.php?fc=module&module=midtranspay&controller=notification`
   * Finish Redirect URL: 
    >`http://[your-site-url]/index.php?fc=module&module=midtranspay&controller=success`
   * Unfinish Redirect URL: 
    >`http://[your-site-url]/index.php?fc=module&module=midtranspay&controller=back`
   * Error Redirect URL: 
    >`http://[your-site-url]/index.php?fc=module&module=midtranspay&controller=failure`

### Customization (Optional, For Developer)
Feel free to change code if needed to customize the module.
If you want to hide Midtrans logo or change any wordings, the frontend files are located here:
* Payment list: `/views/templates/hook/payment.tpl`
* Payment confirmation: `/views/templates/front/payment_execution.tpl`
* Payment page with popup: `/views/templates/front/snappay.tpl`

For example, if you want to remove logo, just look for `Midtrans.png` or change the logo directly at `/logo/` folder.

### Get help

* [SNAP-Prestashop Wiki](https://github.com/veritrans/SNAP-Prestashop/wiki)
* [Veritrans registration](https://dashboard.midtrans.com/register)
* [SNAP documentation](http://snap-docs.midtrans.com)
* Can't find answer you looking for? email to [support@midtrans.com](mailto:support@midtrans.com)
