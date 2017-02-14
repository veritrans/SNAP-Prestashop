<div class="text-center">
  <p>
    <b><h3 class="alert alert-success"> <i class="material-icons">done</i> {l s='Your payment on '} {$shop_name} {' is complete!'}</h3></b>
  </p>
  <p class="warning">
    {l s='If you have questions, comments or concerns, please contact our' mod='midtranspay'} <a href="{$link->getPageLink('contact', true)}">{l s='expert customer support team. ' mod='midtranspay'}</a><br/><br/>
  </p>
  <a href="{$link->getPageLink('history', true)}" title="{l s='Back to orders'}" class="button-exclusive btn btn-primary">{l s='view order history'}</a>
</div>