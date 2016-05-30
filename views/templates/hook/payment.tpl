<div class="row">
  <div class="col-xs-12 col-md-6">
    <p class="payment_module">
      {if version_compare(Configuration::get('PS_INSTALL_VERSION'), '1.5') == -1}
        <a class="bankwire" href="{$base_dir|cat:'modules/midtranspay/payment.php'}" title="Pay Via Midtrans">
      {else}
        <a class="bankwire" href="{$link->getModuleLink('midtranspay', 'payment')}" title="Pay Via Midtrans">
      {/if}
        <img src="{$this_path}Midtrans.png" alt="{l s='Pay via Midtrans' mod='midtranspay'}" height="30px"/>
        {$mt_displayname}
      </a>
    </p>  
  </div>
</div>
