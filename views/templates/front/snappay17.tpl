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
			<p>
				<h3 class="alert alert-info">{l s='Please complete your payment ...' mod='midtranspay'}</h3>
			</p>

			<p>
				{l s='Continue payment via payment popup window.' mod='midtranspay'} <br/>
				{l s='Or click button below:' mod='midtranspay'} <br/><br/>
				
				<a href="#" id='pay-button' title="{l s='Do Payment!'}" class="btn btn-success"> &nbsp; {l s='Loading Payment..'} </a> 			<br/><br/>

				{l s='If you have questions, comments or concerns, please contact our' mod='midtranspay'} <a href="{$link->getPageLink('contact', true)}">{l s='customer support team' mod='midtranspay'}</a><br/><br/>
			</p>

		{else}
			<p>
				<h3 class="alert alert-danger"> <i class="material-icons">warning</i> {l s='Payment Error!' mod='midtranspay'}</h3>
			</p>

			<p class="warning">
				{l s='We noticed a problem with your order. Please do re-checkout.
				If you think this is an error, feel free to contact our' mod='midtranspay'} <a href="{$link->getPageLink('contact', true)}">{l s='expert customer support team' mod='midtranspay'}</a> <br/><br/>
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

{literal} 
<!-- start Mixpanel -->
<script data-cfasync="false" type="text/javascript">(function(e,a){if(!a.__SV){var b=window;try{var c,l,i,j=b.location,g=j.hash;c=function(a,b){return(l=a.match(RegExp(b+"=([^&]*)")))?l[1]:null};g&&c(g,"state")&&(i=JSON.parse(decodeURIComponent(c(g,"state"))),"mpeditor"===i.action&&(b.sessionStorage.setItem("_mpcehash",g),history.replaceState(i.desiredHash||"",e.title,j.pathname+j.search)))}catch(m){}var k,h;window.mixpanel=a;a._i=[];a.init=function(b,c,f){function e(b,a){var c=a.split(".");2==c.length&&(b=b[c[0]],a=c[1]);b[a]=function(){b.push([a].concat(Array.prototype.slice.call(arguments,0)))}}var d=a;"undefined"!==typeof f?d=a[f]=[]:f="mixpanel";d.people=d.people||[];d.toString=function(b){var a="mixpanel";"mixpanel"!==f&&(a+="."+f);b||(a+=" (stub)");return a};d.people.toString=function(){return d.toString(1)+".people (stub)"};k="disable time_event track track_pageview track_links track_forms register register_once alias unregister identify name_tag set_config reset people.set people.set_once people.increment people.append people.union people.track_charge people.clear_charges people.delete_user".split(" ");for(h=0;h<k.length;h++)e(d,k[h]);a._i.push([b,c,f])};a.__SV=1.2;b=e.createElement("script");b.type="text/javascript";b.async=!0;b.src="undefined"!==typeof MIXPANEL_CUSTOM_LIB_URL?MIXPANEL_CUSTOM_LIB_URL:"file:"===e.location.protocol&&"//cdn.mxpnl.com/libs/mixpanel-2-latest.min.js".match(/^\/\//)?"https://cdn.mxpnl.com/libs/mixpanel-2-latest.min.js":"//cdn.mxpnl.com/libs/mixpanel-2-latest.min.js";c=e.getElementsByTagName("script")[0];c.parentNode.insertBefore(b,c)}})(document,window.mixpanel||[]);mixpanel.init("{/literal}{$mixpanel_key}{literal}");</script>
<!-- end Mixpanel -->
{/literal}

<script data-cfasync="false" type="text/javascript">
var mainMidtransScriptRan = false;
var mainMidtransScript = function(event) { 
	if(mainMidtransScriptRan){ return false};
	mainMidtransScriptRan = true;
	var payButton = document.getElementById('pay-button');
	function MixpanelTrackResult(token, merchant_id, cms_name, cms_version, plugin_name, plugin_version, status, result) {
		var eventNames = {
			pay: 'pg-pay',
			success: 'pg-success',
			pending: 'pg-pending',
			error: 'pg-error',
			close: 'pg-close'
		};
		mixpanel.track(
			eventNames[status], 
			{
				merchant_id: merchant_id,
				cms_name: cms_name,
				cms_version: cms_version,
				plugin_name: plugin_name,
				plugin_version: plugin_version,
				snap_token: token,
				payment_type: result ? result.payment_type: null,
				order_id: result ? result.order_id: null,
				status_code: result ? result.status_code: null,
				gross_amount: result && result.gross_amount ? Number(result.gross_amount) : null,
			}
		);
	}

	var SNAP_TOKEN = "{$snap_token}";
	var MERCHANT_ID = "{$merchant_id}";
	var CMS_NAME = "prestashop";
	var CMS_VERSION = "{$cms_version}";
	var PLUGIN_NAME = "prestashop_main";
	var PLUGIN_VERSION = "{$plugin_version}";

	// Continously retry to execute SNAP popup if fail, with 1000ms delay between retry
	var execCount = 0;
	function execSnapCont(){
		var baseRedirectUrl = "{$moduleSuccessUrl|unescape:'htmlall'}";
		var baseFailureRedirectUrl = "{$moduleFailureUrl|unescape:'htmlall'}"
		try{
			var lastUrlFragment = baseRedirectUrl.split('/').pop();
			var isContainsGetParam = lastUrlFragment.indexOf('?') > 0;
			if(!isContainsGetParam){
				baseRedirectUrl = baseRedirectUrl+'?';
				baseFailureRedirectUrl = baseFailureRedirectUrl+'?';
			}
		} catch(e){}

		var callbackTimer = setInterval(function() {
			var snapExecuted = false;
			try{
				console.log('Popup attempt:',++execCount);
				snap.pay(SNAP_TOKEN , 
				{
					skipOrderSummary: true,
					onSuccess: function(result){
						MixpanelTrackResult(SNAP_TOKEN, MERCHANT_ID, CMS_NAME, CMS_VERSION, PLUGIN_NAME, PLUGIN_VERSION, 'success', result);
						console.log(result?result:'no result');
						payButton.innerHTML = 'Loading...';
						window.location = baseRedirectUrl+"&order_id="+result.order_id+"&status_code="+result.status_code+"&transaction_status="+result.transaction_status;
					},
			        onPending: function(result){
			        	MixpanelTrackResult(SNAP_TOKEN, MERCHANT_ID, CMS_NAME, CMS_VERSION, PLUGIN_NAME, PLUGIN_VERSION, 'pending', result);
						console.log(result?result:'no result');
			        	if (result.fraud_status == 'challenge'){ // if challenge redirect to finish
			        		payButton.innerHTML = 'Loading...';
							window.location = baseRedirectUrl+"&order_id="+result.order_id+"&status_code="+result.status_code+"&transaction_status="+result.transaction_status;
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
						MixpanelTrackResult(SNAP_TOKEN, MERCHANT_ID, CMS_NAME, CMS_VERSION, PLUGIN_NAME, PLUGIN_VERSION, 'error', result);
						console.log(result?result:'no result');
						payButton.innerHTML = 'Loading...';
						window.location = baseFailureRedirectUrl+"&order_id="+result.order_id+"&status_code="+result.status_code+"&transaction_status="+result.transaction_status;
					},
					onClose: function(){
						MixpanelTrackResult(SNAP_TOKEN, MERCHANT_ID, CMS_NAME, CMS_VERSION, PLUGIN_NAME, PLUGIN_VERSION, 'close', null);
					}

				});
				snapExecuted = true; // if SNAP popup executed, change flag to stop the retry.
			} catch (e){ 
				if(execCount >= 20){
					location.reload(); payButton.innerHTML = "Loading..."; return;
				}
				console.log(e);
				console.log('Snap s.goHome not ready yet... Retrying in 1000ms!');
			}
			if (snapExecuted) {
				// record 'pay' event to Mixpanel
				MixpanelTrackResult(SNAP_TOKEN, MERCHANT_ID, CMS_NAME, CMS_VERSION, PLUGIN_NAME, PLUGIN_VERSION, 'pay', null);
				clearInterval(callbackTimer);
			}
		}, 1000);
	};
	var clickCount = 0;
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
};

document.addEventListener("DOMContentLoaded", mainMidtransScript(event));
setTimeout(function(){ console.log('calling'); mainMidtransScript(null); }, 30000);  // failover, run main script if it never ran
</script>
{/block}
