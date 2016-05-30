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
		<h3 class="page-subheading">{l s='Payment via Midtrans.' mod='midtranspay'}</h3>
		{* <form action="{$url}" method="post" name="payment_form" class="std"> *}
		{if (version_compare(Configuration::get('PS_VERSION_DB'), '1.5') == -1)}
			<form action="{$base_dir|cat:'modules/midtranspay/validation.php'}" method="post" class="std"> 
		{else}
			<form action="{$link->getModuleLink('midtranspay', 'validation', [], true)}" method="post" class="std"> 
		{/if}
			<p>
				<img src="{$this_path}Midtrans.png" alt="{l s='Midtrans' mod='midtranspay'}" height="49" style="float:left; margin: 0px 10px 5px 0px;" />
				<br/><b>{l s='You have chosen to pay via Midtrans.' mod='midtranspay'}</b><br/>
			</p>

			<p style="margin-top:20px;">
				- {l s='The total amount of your order is' mod='midtranspay'}
				<span id="amount" class="price">{displayPrice price=$total}</span>
				{if $use_taxes == 1}
		    	{l s='(tax incl.)' mod='midtranspay'}
		    {/if}<br/>
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
				{/if}
			</p>

			{if $payment_type == 'vtdirect'}
				<h3 class="page-subheading">Credit Card Information</h3>
				<div class="form-group">
					<label for="">Credit Card Number</label>	
					<input type="text" class="form-control">
				</div>
				<div class="form-group">
					<label for="">Card Expiry</label>	
					<input type="text" class="form-control">
					<input type="text" class="form-control">
				</div>
				<div class="form-group">
					<label for="">CVV</label>
					<input type="text" class="form-control">
				</div>					
			{/if}

			<h3 class="page-subheading">Confirm Order</h3>
			
			<p class="cart_navigation clearfix" id="cart_navigation">
				<a href="{$link->getPageLink('order', true, NULL, "step=3")}" class="button_large">{l s='Other payment methods' mod='midtranspay'}
				</a>
				<input type="submit" value="{l s='Place my order' mod='midtranspay'}" class="exclusive_large" />
			</p>
		</form>
	{/if}
</div>