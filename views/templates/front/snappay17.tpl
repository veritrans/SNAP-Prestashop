{extends "$layout"}
{block name="content"}
<script data-cfasync="false" src="{$snap_script_url}" data-client-key="{$client_key}" id="snap_script"></script>
<section id="main">
	<header class="page-header">
		<h1>{l s='Payment Step' mod='midtranspay'}</h1>
	</header>
	<section id="content" class="page-content page-cms">
		<h3 class="page-subheading">
			{l s='Payment via Midtrans.' mod='midtranspay'}
			<img src="{$this_path}Midtrans.png" alt="{l s='midtrans' mod='midtranspay'}" width="120" height="21" style=" float:left; margin: 0px 10px 5px 0px;" />
		</h3><br/>

		<div class="text-xs-center" id="payment-notice">
		{if $status == 'token_exist'}
			<!-- <script src="https://app.sandbox.veritrans.co.id/snap/snap.js"></script> -->
			<p>
				<h3 class="alert alert-info">{l s='Please complete your payment ...' mod='midtranspay'}</h3>
			</p>

			<p>
				{l s='Continue payment via payment popup window.' mod='midtranspay'} <br/>
				{l s='Or click button below:' mod='midtranspay'} <br/><br/>
				
				<a href="#" id='pay-button' title="{l s='Do Payment!'}" class="btn btn-success"> &nbsp; {l s='Loading Payment..'} </a> 			<br/><br/>

				{l s='If you have questions, comments or concerns, please contact our' mod='midtranspay'} <a href="{$link->getPageLink('contact', true)}">{l s='customer support team. ' mod='midtranspay'}</a><br/><br/>
			</p>

		{else}
			<p>
				<h3 class="alert alert-danger"> <i class="material-icons">warning</i> {l s='Payment Error!' mod='midtranspay'}</h3>
			</p>

			<p class="warning">
				{l s='We noticed a problem with your order. Please do re-checkout.
				If you think this is an error, feel free to contact our' mod='midtranspay'} <a href="{$link->getPageLink('contact', true)}">{l s='expert customer support team. ' mod='midtranspay'}</a> <br/><br/>
			</p>
			<a class="btn btn-primary" href="{$link->getPageLink('order', true, NULL, "submitReorder&id_order={$order_id|intval}")|escape:'html':'UTF-8'}" title="{l s='Re-Checkout'}"> 
			<i class="material-icons">refresh</i>&nbsp;{l s='Re-Checkout'}</a>
		{/if}

		</div>
		
		<div class="text-xs-center" id="pending-notice" style="display:none;">
			<p>
				<h3 class="alert alert-info"> <i class="material-icons">schedule</i> {l s='Awaiting your payment ... '}</h3>
			</p>
			<p class="warning">
				{l s='Please complete your payment as instructed before. You can also check your email for instruction. Thank You!'}
			</p>

			<a  target="_blank" href="#" id='instruction-button' title="{l s='View Payment Instruction'}" class="button-exclusive btn btn-success">{l s='View Payment Instruction'} <i class="icon-chevron-right right"></i></a>
		</div> <br/><br/><br/>

	</section>

</section>


<script data-cfasync="false" type="text/javascript">
document.addEventListener("DOMContentLoaded", function(event) { 
	//* #############======= Load JS with JS way, no need js load from php or JQuery - simpler version ======= Worked with some retry
	function loadExtScript(src) {
		// if snap.js is loaded from html script tag, don't load again
		if (document.getElementById('snap_script'))
			return;
		// Append script to doc
		var s = document.createElement('script');
		s.src = src;
		a = document.body.appendChild(s);
		a.setAttribute('data-client-key',"{$client_key}");
		a.setAttribute('data-cfasync','false');
	}

	// Continously retry to execute SNAP popup if fail, with 1000ms delay between retry
	function execSnapCont(){
		var callbackTimer = setInterval(function() {
			var snapExecuted = false;
			try{
				snap.pay("{$snap_token}", 
				{
					skipOrderSummary: true,
					onSuccess: function(result){
						console.log(result);
						window.location = "{$moduleSuccessUrl}?&order_id="+result.order_id+"&status_code="+result.status_code+"&transaction_status="+result.transaction_status;
					},
			        onPending: function(result){
			        	
			        	if (result.fraud_status == 'challenge'){ // if challenge redirect to finish
							window.location = "{$moduleSuccessUrl}?&order_id="+result.order_id+"&status_code="+result.status_code+"&transaction_status="+result.transaction_status;
						}
						if (typeof result.pdf_url == 'undefined'){ // if no link, hide btn
							document.getElementById('instruction-button').style.display = "none";
						} else {
			        		document.getElementById('instruction-button').href = result.pdf_url;
						}
			        	document.getElementById('payment-notice').style.display = "none";
			        	document.getElementById('pending-notice').style.display = "block";
			        },
					onError: function(result){
						console.log(result);
						window.location = "{$moduleFailureUrl}?&order_id="+result.order_id+"&status_code="+result.status_code+"&transaction_status="+result.transaction_status;
					}

				});
				snapExecuted = true; // if SNAP popup executed, change flag to stop the retry.
			} catch (e){ 
				console.log(e);
				console.log('Snap s.goHome not ready yet... Retrying in 1000ms!');
			}
			if (snapExecuted) {
				clearInterval(callbackTimer);
			}
		}, 1000);
	};

	console.log('Loading snap JS library now!');
	// Loading SNAP JS Library to the page		
	loadExtScript('{$snap_script_url}');
	console.log('Snap library is loaded now');
	/**
	 */
	var clickCount = 0;
	var payButton = document.getElementById('pay-button');
	payButton.innerHTML = '<i class="material-icons">payment</i> &nbsp; {l s="Proceed to Payment"} <i class="material-icons">chevron_right</i>';
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
{/block}
