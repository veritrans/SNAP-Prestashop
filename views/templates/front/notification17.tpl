{extends "$layout"}
{block name="content"}
<section id="main">
	<header class="page-header">
		<h2>{l s='Order summary' mod='midtranspay'}</h2>
	</header>
	<section id="content" class="page-content page-cms">
		<h3 class="page-subheading">
			{l s='Payment via Midtrans.' mod='midtranspay'}
			<img src="{$this_path}Midtrans.png" alt="{l s='midtrans' mod='midtranspay'}" width="120" height="21" style=" float:left; margin: 0px 10px 5px 0px;" />
		</h3> <br/>

		<div class="text-xs-center">
		{if ($status == 'success' || $smarty.get.status_code|default:0 == '200' || $smarty.get.status_code|default:0 == '201')}
			<p>
				<b><h3 class="alert alert-success">{l s='Your payment on '} {$shop_name} {' is complete!'}</h3></b>
			</p>
			<p class="warning">
				{l s='If you have questions, comments or concerns, please contact our' mod='midtranspay'} <a href="{$link->getPageLink('contact', true)}">{l s='expert customer support team. ' mod='midtranspay'}</a><br/><br/>
			</p>
			<a href="{$link->getPageLink('history', true)}" title="{l s='Back to orders'}" class="button-exclusive btn btn-primary">{l s='view order history'}</a>
		{elseif $status == 'back'}
			<p>
				<b><h3 class="alert alert-info">{l s='We havent received your payment' mod='midtranspay'}</h3></b>
			</p>
			<p class="warning">
				{l s='If you want to go to your cart, please do re-checkout.'}
			</p>
			<a class="button" href="{$link->getPageLink('order', true, NULL, "submitReorder&id_order={$order_id|default:0|intval}")|escape:'html':'UTF-8'}" title="{l s='Re-Checkout'}"> 
			<i class="icon-refresh"></i>&nbsp;{l s='Re-Checkout'}</a>
		{elseif $status == 'pending'}
			<p>
				<b><h3 class="alert alert-info">{l s='Awaiting your payment' mod='midtranspay'}</h3></b>
			</p>
			<p class="warning">
				{l s='Please complete your payment as instructed before. Check your email for instruction. Thank You!'}
			</p>
		{else}
			<p>
				<b><h3 class="alert alert-danger">{l s='Payment Error!' mod='midtranspay'}</h3></b>
			</p>
			<p class="warning">
				{l s='We noticed a problem with your order. Please do re-checkout.
				If you think this is an error, feel free to contact our' mod='midtranspay'} <a href="{$link->getPageLink('contact', true)}">{l s='expert customer support team. ' mod='midtranspay'}</a> <br/><br/>
			</p>
			<a class="btn btn-primary" href="{$link->getPageLink('order', true, NULL, "submitReorder&id_order={$order_id|default:0|intval}")|escape:'html':'UTF-8'}" title="{l s='Re-Checkout'}"> 
			<i class="material-icons">refresh</i>&nbsp;{l s='Re-Checkout'}</a>
		{/if}
		</div> <br/><br/><br/>
	</section>
</section>
{/block}