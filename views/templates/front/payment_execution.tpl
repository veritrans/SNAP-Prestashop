{capture name=path}{l s='Midtrans Payment' mod='midtranspay'}{/capture}
{* {include file="$tpl_dir./breadcrumb.tpl"} *}

<h2 class="page-heading">{l s='Order Summary' mod='midtranspay'}</h2>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

<div class="box cheque-box">
	{if $nbProducts <= 0}
		<p class="warning">{l s='Your shopping cart is empty.' mod='midtranspay'}</p>
	{elseif $error_message != ""}
		<p class="warning">{$error_message}</p>
	{else}
		<h3 id="loading-text" class="page-subheading">{l s='Payment via Midtrans.' mod='midtranspay'}</h3>
			{* Additional feature check *}

			{if isset($smarty.get.feature)}
				<form action="{$link->getModuleLink('midtranspay', 'validation', ['feature' => $smarty.get.feature], true)}" method="post" class="std"> 
			{else}
				<form action="{$link->getModuleLink('midtranspay', 'validation', [], true)}" method="post" class="std"> 
			{/if}

			<p>
				<img src="{$this_path}Midtrans.png" alt="{l s='Midtrans' mod='midtranspay'}" height="49" style="margin: 0px 10px 5px 0px;" />
			</p>

			<p style="margin-top:10px;">
				<b>{l s='You have chosen to pay via Midtrans.' mod='midtranspay'}</b>
				<br/>
				- {l s='The total amount of your order is' mod='midtranspay'}
					<span id="amount" class="price">{displayPrice price=$total}</span>
				{if $use_taxes == 1}
		    		{l s='(tax incl.)' mod='midtranspay'}
		   		{/if}
		   			<br/>
				-
				{if $currencies|@count > 1}
					{l s='We allow several currencies to be sent via Midtrans.' mod='midtranspay'}
					<br /><br />
					{l s='Choose one of the following:' mod='midtranspay'}
					<select id="currency_payement" name="currency_payement" onchange="setCurrency($('#currency_payement').val());">
						{foreach from=$currencies item=currency}
							<option value="{$currency.id_currency}" {if $currency.id_currency == $cust_currency}selected="selected"{/if}>{$currency.name}</option>
						{/foreach}
					</select>
				{else}
					{l s='We allow the following currency to be sent via Midtrans:' mod='midtranspay'}&nbsp;<b>{$currencies.0.name}</b>
					<input type="hidden" name="currency_payement" value="{$currencies.0.id_currency}" />
					<script type="text/javascript">
						// Auto submit to cut unnecessary step
    					setTimeout(function(){ 
    						document.getElementById('place-order').click(); 
    						document.getElementById('cart_navigation').style.visibility = "hidden"; 
    						document.getElementById('loading-text').innerHTML = "Loading... Payment via Midtrans."; 
    					}, 100); 
					</script>
				{/if}
			</p>

			<h3 class="page-subheading">Confirm Order</h3>
			
			<p class="cart_navigation" id="cart_navigation">
				<a href="{$link->getPageLink('order', true, NULL, "step=3")}" class="button-exclusive btn btn-default"><i class="icon-chevron-left"></i>{l s='Other payment methods' mod='midtranspay'}
				</a>
				<button type="submit" id="place-order" class="button btn btn-default button-medium" /><span>{l s='Place my order' mod='midtranspay'}</span></button>
			</p>
		</form>
	{/if}
</div>