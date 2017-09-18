{capture name=path}{l s='Midtrans payment.' mod='midtranspay'}{/capture}
<script src="{$snap_script_url}" data-client-key="{$client_key}" id="snap_script"></script>
<h2>{l s='Order summary' mod='midtranspay'}</h2>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

<h3 class="page-subheading">{l s='Payment via Midtrans.' sprintf = $status mod='midtranspay'}
<!-- <form action="{$link->getModuleLink('midtranspay', 'validation', [], true)}" method="post"> -->
<img src="{$this_path}Midtrans.png" alt="{l s='midtrans' mod='midtranspay'}" width="120" height="21" style=" float:left; margin: 0px 10px 5px 0px;" /></h3> <br/>

<div class="text-center" id="payment-notice">
{if $status == 'token_exist'}
	<!-- <script src="https://app.sandbox.veritrans.co.id/snap/snap.js"></script> -->
	<p>
		<b><h3 class="alert alert-info">{l s='Please complete your payment...' mod='midtranspay'}</h3></b>
	</p>

	<p>
		
	</p>

	<h4 class="warning">
		{l s='Continue payment via payment popup window.' mod='midtranspay'} <br/>
		{l s='Or click button below:' mod='midtranspay'} <br/><br/>
		<a href="#" id='pay-button' title="{l s='Do Payment!'}" class="btn btn-success"> &nbsp; {l s='Loading Payment..'} </a>
		<br/><br/>
		{l s='If you have questions, comments or concerns, please contact our' mod='midtranspay'} <a href="{$link->getPageLink('contact', true)}">{l s='customer support team. ' mod='midtranspay'}</a>.<br/><br/>
	</h4>


{else}
	<p>
		<b><h3 class="alert alert-danger">{l s='Payment Error!' mod='midtranspay'}</h3></b>
	</p>
	<p class="warning">
		{l s='We noticed a problem with your order. Please do re-checkout.
		If you think this is an error, feel free to contact our' mod='midtranspay'} <a href="{$link->getPageLink('contact', true)}">{l s='expert customer support team. ' mod='midtranspay'}</a> <br/><br/>
	</p>
	<a class="button" href="{$link->getPageLink('order', true, NULL, "submitReorder&id_order={$order_id|intval}")|escape:'html':'UTF-8'}" title="{l s='Re-Checkout'}"> 
	<i class="icon-refresh"></i>&nbsp;{l s='Re-Checkout'}</a>
{/if}

<!-- <h2>{l s='Your token is %s' sprintf= $snap_token mod='midtranspay'}</h2> -->

</div>

<div class="text-center" id="pending-notice" style="display:none;">
	<p>
		<b><h3 class="alert alert-info">{l s='Awaiting your payment' mod='midtranspay'}</h3></b>
	</p>
	<h4 class="warning">
		{l s='Please complete your payment as instructed before. You can also check your email for instruction. Thank You!'}
	</h4>

	<a  target="_blank" href="#" id='instruction-button' title="{l s='View Payment Instruction'}" class="button-exclusive btn btn-success">{l s='View Payment Instruction'} <i class="icon-chevron-right right"></i></a>
</div>

<br/><br/><br/>


<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(event) {
	var execCount = 0;
	function execSnapCont(){
		var callbackTimer = setInterval(function() {
			var snapExecuted = false;
			try{
				console.log('Popup attempt:',++execCount);
				snap.pay("{l s='%s' sprintf= $snap_token mod='midtranspay'}", 
				{
					skipOrderSummary: true,
					onSuccess: function(result){
						console.log(result);
						window.location = "{$moduleSuccessUrl|unescape:'htmlall'}?&order_id="+result.order_id+"&status_code="+result.status_code+"&transaction_status="+result.transaction_status;
					},
			        onPending: function(result){
			        	
			        	if (result.fraud_status == 'challenge'){ // if challenge redirect to finish
							window.location = "{$moduleSuccessUrl|unescape:'htmlall'}?&order_id="+result.order_id+"&status_code="+result.status_code+"&transaction_status="+result.transaction_status;
						}
						if (typeof result.pdf_url == 'undefined'){ // if no link, hide btn
							document.getElementById('instruction-button').style.display = "none";
						} else {
			        		document.getElementById('instruction-button').href = result.pdf_url;
			        		// window.open(result.pdf_url,'_blank');
						}
			        	document.getElementById('payment-notice').style.display = "none";
			        	document.getElementById('pending-notice').style.display = "block";
			        },
					onError: function(result){
						console.log(result);
						window.location = "{$moduleFailureUrl|unescape:'htmlall'}?&order_id="+result.order_id+"&status_code="+result.status_code+"&transaction_status="+result.transaction_status;
					}

				});
				snapExecuted = true; // if SNAP popup executed, change flag to stop the retry.
			} catch (e){ 
				console.log(e);
				console.log('Exception when calling snap.pay()... Retrying in 1000ms!');
			}
			if (snapExecuted) {
				clearInterval(callbackTimer);
			}
		}, 1000);
	};

	var clickCount = 0;
	var payButton = document.getElementById('pay-button');
	payButton.innerHTML = 'Proceed to Payment <i class="icon-chevron-right right"></i>';
	payButton.onclick = function(){
		if(clickCount >= 2){
			location.reload();
			payButton.innerHTML = 'Loading...';
			return;
		}
		execSnapCont();
		clickCount++;
	};
	// Call execSnapCont() 
	execSnapCont();
});
</script>
