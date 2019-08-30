{capture name=path}{l s='Midtrans payment.' mod='midtranspay'}{/capture}
<script data-cfasync="false" src="{$snap_script_url}" data-client-key="{$client_key}" id="snap_script"></script>
<h2>{l s='Order summary' mod='midtranspay'}</h2>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

<h3 class="page-subheading">{l s='Payment via Midtrans.' sprintf = $status mod='midtranspay'}
<!-- <form action="{$link->getModuleLink('midtranspay', 'validation', [], true)}" method="post"> -->
<img src="{$this_path}Midtrans.png" alt="{l s='midtrans' mod='midtranspay'}" width="120" height="21" style=" float:left; margin: 0px 10px 5px 0px;" /></h3> <br/>

<div class="text-center" id="payment-notice">
{if $status == 'token_exist'}
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
		{l s='If you have questions, comments or concerns, please contact our' mod='midtranspay'} <a href="{$link->getPageLink('contact', true)}">{l s='customer support team' mod='midtranspay'}</a>.<br/><br/>
	</h4>


{else}
	<p>
		<b><h3 class="alert alert-danger">{l s='Payment Error!' mod='midtranspay'}</h3></b>
	</p>
	<p class="warning">
		{l s='We noticed a problem with your order. Please do re-checkout.
		If you think this is an error, feel free to contact our' mod='midtranspay'} <a href="{$link->getPageLink('contact', true)}">{l s='expert customer support team' mod='midtranspay'}</a> <br/><br/>
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

{literal} 
<!-- start Mixpanel -->
<script data-cfasync="false" type="text/javascript">(function(e,a){if(!a.__SV){var b=window;try{var c,l,i,j=b.location,g=j.hash;c=function(a,b){return(l=a.match(RegExp(b+"=([^&]*)")))?l[1]:null};g&&c(g,"state")&&(i=JSON.parse(decodeURIComponent(c(g,"state"))),"mpeditor"===i.action&&(b.sessionStorage.setItem("_mpcehash",g),history.replaceState(i.desiredHash||"",e.title,j.pathname+j.search)))}catch(m){}var k,h;window.mixpanel=a;a._i=[];a.init=function(b,c,f){function e(b,a){var c=a.split(".");2==c.length&&(b=b[c[0]],a=c[1]);b[a]=function(){b.push([a].concat(Array.prototype.slice.call(arguments,0)))}}var d=a;"undefined"!==typeof f?d=a[f]=[]:f="mixpanel";d.people=d.people||[];d.toString=function(b){var a="mixpanel";"mixpanel"!==f&&(a+="."+f);b||(a+=" (stub)");return a};d.people.toString=function(){return d.toString(1)+".people (stub)"};k="disable time_event track track_pageview track_links track_forms register register_once alias unregister identify name_tag set_config reset people.set people.set_once people.increment people.append people.union people.track_charge people.clear_charges people.delete_user".split(" ");for(h=0;h<k.length;h++)e(d,k[h]);a._i.push([b,c,f])};a.__SV=1.2;b=e.createElement("script");b.type="text/javascript";b.async=!0;b.src="undefined"!==typeof MIXPANEL_CUSTOM_LIB_URL?MIXPANEL_CUSTOM_LIB_URL:"file:"===e.location.protocol&&"//cdn.mxpnl.com/libs/mixpanel-2-latest.min.js".match(/^\/\//)?"https://cdn.mxpnl.com/libs/mixpanel-2-latest.min.js":"//cdn.mxpnl.com/libs/mixpanel-2-latest.min.js";c=e.getElementsByTagName("script")[0];c.parentNode.insertBefore(b,c)}})(document,window.mixpanel||[]);mixpanel.init("{/literal}{$mixpanel_key}{literal}");</script>
<!-- end Mixpanel -->
{/literal}

<script data-cfasync="false" type="text/javascript">
var mixpanel = mixpanel ? mixpanel : { init : function(){}, track : function(){} };

var mainMidtransScriptRan = false ;
var mainMidtransScript = function(event) {
	if(mainMidtransScriptRan){ return false};
	mainMidtransScriptRan = true;
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

	var SNAP_TOKEN = "{$snap_token|escape:'htmlall'}";
	var MERCHANT_ID = "{$merchant_id|escape:'htmlall'}";
	var CMS_NAME = "prestashop";
	var CMS_VERSION = "{$cms_version|escape:'htmlall'}";
	var PLUGIN_NAME = "prestashop_main";
	var PLUGIN_VERSION = "{$plugin_version|escape:'htmlall'}";

	var execCount = 0;
	var snapExecuted = false;
	var intervalFunction = 0;

	function execSnapCont(){
		var baseRedirectUrl = "{$moduleSuccessUrl|unescape:'htmlall' nofilter}";
		var baseFailureRedirectUrl = "{$moduleFailureUrl|unescape:'htmlall' nofilter}";
		try{
			var locationUrl = document.createElement("a");
			locationUrl.href = baseRedirectUrl;
			var isContainsQueryString = locationUrl.search.length > 0;
			if(!isContainsQueryString){
				baseRedirectUrl = baseRedirectUrl+'?';
				baseFailureRedirectUrl = baseFailureRedirectUrl+'?';
			}
		} catch(e){}

		intervalFunction = setInterval(function() {
			try{
				console.log('Popup attempt:',++execCount);
				snap.pay(SNAP_TOKEN , 
				{
					skipOrderSummary: true,
					onSuccess: function(result){
						MixpanelTrackResult(SNAP_TOKEN, MERCHANT_ID, CMS_NAME, CMS_VERSION, PLUGIN_NAME, PLUGIN_VERSION, 'success', result);
						console.log(result?result:'no result');
{if $isUsingMAPFinishUrl}
						window.location = result.finish_redirect_url;
{else}
						{literal}
						window.location = baseRedirectUrl+"&order_id="+result.order_id+"&status_code="+result.status_code+"&transaction_status="+result.transaction_status; //literal
						{/literal}
{/if}
					},
			        onPending: function(result){
						MixpanelTrackResult(SNAP_TOKEN, MERCHANT_ID, CMS_NAME, CMS_VERSION, PLUGIN_NAME, PLUGIN_VERSION, 'pending', result);
						console.log(result?result:'no result');
			        	if (result.fraud_status == 'challenge'){ // if challenge redirect to finish
			        		{literal}
							window.location = baseRedirectUrl+"&order_id="+result.order_id+"&status_code="+result.status_code+"&transaction_status="+result.transaction_status;
							{/literal}
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
						MixpanelTrackResult(SNAP_TOKEN, MERCHANT_ID, CMS_NAME, CMS_VERSION, PLUGIN_NAME, PLUGIN_VERSION, 'error', result);
						console.log(result?result:'no result');
						{literal}
						window.location = baseFailureRedirectUrl+"&order_id="+result.order_id+"&status_code="+result.status_code+"&transaction_status="+result.transaction_status;
						{/literal}
					},
					onClose: function(){
						MixpanelTrackResult(SNAP_TOKEN, MERCHANT_ID, CMS_NAME, CMS_VERSION, PLUGIN_NAME, PLUGIN_VERSION, 'close', null);
					}

				});
				snapExecuted = true; // if SNAP popup executed, change flag to stop the retry.
			} catch (e){ 
				console.log(e);
				if(execCount >= 20){
					location.reload(); payButton.innerHTML = "Loading..."; return;
				}
				console.log('Exception when calling snap.pay()... Retrying in 1000ms!');
			} finally {
				if (snapExecuted) {
					// record 'pay' event to Mixpanel
					MixpanelTrackResult(SNAP_TOKEN, MERCHANT_ID, CMS_NAME, CMS_VERSION, PLUGIN_NAME, PLUGIN_VERSION, 'pay', null);
					clearInterval(intervalFunction);
				}
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
};

document.addEventListener("DOMContentLoaded", mainMidtransScript);
setTimeout(function(){ console.log('calling'); mainMidtransScript(null); }, 30000);  // failover, run main script if it never ran
</script>