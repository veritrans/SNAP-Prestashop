
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

#### Live Demo
Want to see Midtrans Magento payment plugins in action? We have some demo web-stores for Magento that you can use to try the payment journey directly, click the link below.
* [Midtrans CMS Demo Store](https://docs.midtrans.com/en/snap/with-plugins?id=midtrans-payment-plugin-live-demonstration)

### Installation Instruction

#### Minimum Requirements

* Prestashop 1.6 & 1.7 or greater
* PHP version 5.4 or greater
* MySQL version 5.0 or greater

#### Installation & Configuration
1. [Download](../../archive/master.zip) the modules from this repository.
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
    >`http://[your-site-url]/index.php?fc=module&module=midtranspay&controller=success`
   * Error Redirect URL: 
    >`http://[your-site-url]/index.php?fc=module&module=midtranspay&controller=failure`

### Customization (Optional, For Developer)
Feel free to change code if needed to customize the module.
If you want to hide Midtrans logo or change any wordings, the frontend files are located here:
* Payment list: `/views/templates/hook/payment.tpl`
* Payment confirmation: `/views/templates/front/payment_execution.tpl`
* Payment page with popup: `/views/templates/front/snappay.tpl`

For example, if you want to remove logo, just search for `Midtrans.png` within those files or change the logo directly at `/logo/` folder.

#### Separated Payment Buttons
If you do not prefer just 1 single payment button/options ("Online Payments") displayed to your customer, you can enable:
`Enable Separated Bank Transfer Button` from the module configuration menu. It will allow you display additional separated payment buttons like:

<img src="https://user-images.githubusercontent.com/13027142/91282913-250d9c80-e7b4-11ea-90d2-a6516199e9ec.png" width=50% height=50%>
<img src="https://user-images.githubusercontent.com/13027142/91282881-1b843480-e7b4-11ea-8f85-03b15f692201.png" width=35% height=35%>

### Get help

* Please follow [this step by step guide](https://docs.midtrans.com/en/snap/with-plugins?id=prestashop-plugin-installation-and-configuration) for complete configuration. If you have any feedback or request, please [do let us know here](https://docs.midtrans.com/en/snap/with-plugins?id=feedback-and-request).
* [SNAP-Prestashop Wiki](https://github.com/veritrans/SNAP-Prestashop/wiki)
* [Veritrans registration](https://dashboard.midtrans.com/register)
* [SNAP documentation](http://snap-docs.midtrans.com)
* Can't find answer you looking for? email to [support@midtrans.com](mailto:support@midtrans.com)
